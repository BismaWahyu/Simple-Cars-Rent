<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CarController;
use App\Http\Controllers\Api\RentController;

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

Route::group(
    [
        'prefix' => 'car'
    ],
    function(){
        Route::get('/search', [CarController::class, 'search']);
        Route::get('/', [CarController::class, 'index']);
        Route::get('/{id}', [CarController::class, 'detail']);
    }
);

Route::group(
    [
        'prefix' => 'rent'
    ],
    function(){
        Route::get('/', [RentController::class, 'index']);
        Route::get('/{id}', [RentController::class, 'detail']);
        Route::post('/book', [RentController::class, 'store']);
        Route::post('/return', [RentController::class, 'return']);
    }
);

// Route::post('/register', [AuthController::class, 'register']);
// Route::post('/login', [AuthController::class, 'login']);

// Route::middleware('auth:api')->group(function() {
//     Route::get('/logout', [AuthController::class, 'logout']);
// });
