<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileStoreRequest;

class FileController extends Controller
{
    public function upload(FileStoreRequest $request) {
        $fileName = $request->file('file')->getClientOriginalName();
        $fileExt  = $request->file('file')->extension();
        $filePath = 'uploads/'.$request->user()->id;

        $request->file('file')->storeAs($filePath,$fileName);
    }
}
