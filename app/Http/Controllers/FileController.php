<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use App\Http\Requests\EditRequest;
use App\Http\Requests\UploadRequest;
use App\Http\Resources\FileResource;
use App\Models\File;
use App\Models\Right;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FileController extends Controller
{
    public function upload(UploadRequest $request) {
        $files = $request->file('files');
        $user  = $request->user();

        $responses = [];
        foreach ($files as $file) {
            $fileName = $file->getClientOriginalName();
            $fileExt  = $file->extension();
            $filePath = "uploads/$user->id";

            // Валидация файла
            $validator = Validator::make(['file' => $file], [
                'file' => 'max:2048|mimes:doc,pdf,docx,zip,jpeg,jpg,png',
            ]);
            if ($validator->fails()) {
                // Сохранение плохого ответа API
                $responses[] = [
                    'success' => false,
                    'message' => $validator->errors(),
                    'name'    => $fileName,
                ];
                continue;
            }

            // Наименование повторяющихся
            $fileNameNoExt = basename($fileName, ".$fileExt");
            $num = 1;
            while (Storage::exists("$filePath/$fileName")) {
                $fileName = "$fileNameNoExt ($num).$fileExt";
                $num++;
            }

            // Сохранение файла в хранилище
            $file->storeAs($filePath,$fileName);

            // Сохранение записи в БД
            $fileDB= File::create([
                'name'      => $fileName,
                'extension' => $fileExt,
                'path'      => $filePath,
                'file_id' => Str::random(10),
                'user_id' => $user->id,
            ]);

            // Сохранение успешного ответа API
            $responses[] = [
                'success' => true,
                'code'    => 200,
                'message' => 'Success',
                'name'    => $fileDB->name,
                'url'     => route('download', ['id' => $fileDB->file_id]),
                'file_id' => $fileDB->file_id
            ];
        }
        return response($responses);
    }
    public function edit(EditRequest $request, $file_id) {
        $file = File::where('file_id', $file_id)->first();
        if (!$file)
            throw new ApiException(404, 'File not found');

        if ($file->user_id !== Auth::id())
            throw new ApiException(403, 'Forbidden for you');

        $oldPath = "uploads/$file->user_id/$file->name";
        $newPath = "uploads/$file->user_id/$request->name";

        if (Storage::exists($newPath))
            throw new ApiException(409, 'This file name is taken');

        Storage::move($oldPath, $newPath);

        $file->name = $request->name;
        $file->save();

        return response([
            'success' => true,
            'code' => 200,
            'message' => 'Renamed',
        ]);
    }
    public function destroy($file_id) {
        $file = File::where('file_id', $file_id)->first();
        if (!$file)
            throw new ApiException(404, 'File not found');

        if ($file->user_id !== Auth::id())
            throw new ApiException(403, 'Forbidden for you');

        $file->delete();

        return response([
            'success' => true,
            'code' => 200,
            'message' => 'File deleted',
        ]);
    }
    public function download($file_id) {
        $file = File::where('file_id', $file_id)->first();
        if (!$file)
            throw new ApiException(404, 'File not found');

        $coAuthor = Right
            ::where('user_id', Auth::id())
            ->where('file_id', $file->id)
            ->first();

        if ($file->user_id !== Auth::id() || $coAuthor)
            throw new ApiException(403, 'Forbidden for you');

        $path = Storage::disk("local")->path("$file->path/$file->name");

        return response()->download($path, basename($path));
    }
    public function owned() {
        $files = File::where('user_id', Auth::id())->get();
        if (count($files) < 1)
            throw new ApiException(404, 'Owned files not found');

        return response(FileResource::collection($files));
    }
    public function allowed(){
        $rights = Right::where('user_id', Auth::id())->get();
        if (count($rights) < 1)
            throw new ApiException(404, 'Shared files not found');

        foreach ($rights as $right){
            $rightIds[] = $right->file_id;
        }
        $files = File::whereIn('id', $rightIds)->get();

        return response(FileResource::collection($files));
    }
}
