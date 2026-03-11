<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MobileSuitController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ユーザー管理（認証不要）
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ユーザー管理（認証必要）
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::delete('/user', [AuthController::class, 'withdraw']);
});

Route::apiResource('mobile-suits', MobileSuitController::class);