<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use App\Http\Requests\RightChangeRequest;
use App\Http\Resources\CoAuthorResource;
use App\Models\File;
use App\Models\Right;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class RightController extends Controller
{
    public function add(RightChangeRequest $request, $file_id) {
        $file = File::where('file_id', $file_id)->first();
        if (!$file)
            throw new ApiException(404, 'File not found');

        $author = User::find(Auth::id());
        if ($file->user_id !== $author->id)
            throw new ApiException(403, 'Forbidden for you');

        $coAuthor = User::where('email', $request->email)->first();

        $right = Right
            ::where('user_id', $coAuthor->id)
            ->where('file_id', $file->id)
            ->first();
        if ($right)
            throw new ApiException(409, 'Right already exist');

        Right::create([
            'user_id' => $coAuthor->id,
            'file_id' => $file->id,
        ]);

        $rights = Right::where('file_id', $file->id)->get();

        return response([
            [
                'fullname' => "$author->first_name $author->last_name",
                'email'     => $author->email,
                'type'      => 'author',
                'code'      => 200,
            ],
            ...CoAuthorResource::collection($rights),
        ]);
    }public function destroy(RightChangeRequest $request, $file_id) {
        $file = File::where('file_id', $file_id)->first();
        if (!$file)
            throw new ApiException(404, 'File not found');

        $author = User::find(Auth::id());
        if ($file->user_id !== $author->id)
            throw new ApiException(403, 'Forbidden for you');

        $coAuthor = User::where('email', $request->email)->first();

        $right = Right
            ::where('user_id', $coAuthor->id)
            ->where('file_id', $file->id)
            ->first();
        if (!$right)
            throw new ApiException(409, 'Right already deleted');

        $right->delete();

        $rights = Right::where('file_id', $file->id)->get();

        return response([
            [
                'fullname' => "$author->first_name $author->last_name",
                'email'     => $author->email,
                'type'      => 'author',
                'code'      => 200,
            ],
            ...CoAuthorResource::collection($rights),
        ]);
    }
}
