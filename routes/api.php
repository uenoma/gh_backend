<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GameSessionController;
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

    // ゲームセッション（認証必要）
    Route::post('/game-sessions', [GameSessionController::class, 'store']);
    Route::patch('/game-sessions/{id}', [GameSessionController::class, 'update']);
    Route::delete('/game-sessions/{id}', [GameSessionController::class, 'destroy']);
});

// ゲームセッション（認証不要）
Route::get('/game-sessions', [GameSessionController::class, 'index']);
Route::get('/game-sessions/{id}', [GameSessionController::class, 'show']);

Route::apiResource('mobile-suits', MobileSuitController::class);