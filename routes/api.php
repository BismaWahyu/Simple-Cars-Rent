<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;

Route::group(
    [
        'prefix' => 'auth'
    ],
    function(){
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    }
);

Route::group(
    [
        'prefix' => 'user'
    ],
    function(){
        Route::get('/profile', [UserController::class, 'me']);
    }
);

// Route::post('/register', [AuthController::class, 'register']);
// Route::post('/login', [AuthController::class, 'login']);

// Route::middleware('auth:api')->group(function() {
//     Route::get('/logout', [AuthController::class, 'logout']);
// });
