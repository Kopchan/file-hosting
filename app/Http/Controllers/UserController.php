<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use App\Http\Requests\AuthRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function signUp(AuthRequest $request){

        return response([
            'success' => true,
            'code' => 201,
            'message' => 'Success',
            'token' => User::create($request->all())->generateToken()
        ], 201);
    }
    public function logIn(LoginRequest $request) {
        $credentials = request(['email', 'password']);

        if (!Auth::attempt($credentials))
            throw new ApiException(401, 'Authorization failed');

        return response([
            'success' => true,
            'code' => 200,
            'message' => 'Success',
            'token' => Auth::user()->generateToken()
        ]);
    }
    public function logOut(Request $request) {
        $request->user()->forceFill(['remember_token' => ''])->save();
    }
}
