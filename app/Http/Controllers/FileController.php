<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadRequest;
use App\Models\File;
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
}
