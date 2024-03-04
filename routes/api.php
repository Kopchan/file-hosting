<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\UserController;
use \App\Http\Controllers\FileController;
use \App\Http\Controllers\RightController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::post('authorization', [UserController::class, 'logIn' ]);
Route::post('registration' , [UserController::class, 'signUp']);

Route::middleware('auth:api')->group(function () {
    Route::get('logout', [UserController::class, 'logOut']);

    Route::group([
        'controller' => FileController::class,
        'prefix' => 'files',
    ], function () {
        Route::get('disk'  , 'owned'  );
        Route::get('shared', 'allowed');

        Route::post  (''    , 'upload'  );
        Route::get   ('{id}', 'download')->name('download');
        Route::patch ('{id}', 'edit'    );
        Route::delete('{id}', 'destroy' );
    });

    Route::group([
        'controller' => RightController::class,
        'prefix' => 'files',
    ], function () {
        Route::post  ('{id}/accesses', 'add'    );
        Route::delete('{id}/accesses', 'destroy');
    });
});
