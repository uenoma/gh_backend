<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatChannelController;
use App\Http\Controllers\ChatMessageController;
use App\Http\Controllers\GameSessionController;
use App\Http\Controllers\MobileSuitController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ユーザー管理（認証不要）
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// ユーザー管理（認証必要）
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::delete('/user', [AuthController::class, 'withdraw']);
    Route::put('/user/password', [AuthController::class, 'changePassword']);

    // チャットチャンネル（認証必要）
    Route::get('/chat-channels', [ChatChannelController::class, 'index']);
    Route::get('/chat-channels/{id}', [ChatChannelController::class, 'show']);
    Route::post('/chat-channels', [ChatChannelController::class, 'store']);
    Route::patch('/chat-channels/{id}', [ChatChannelController::class, 'update']);
    Route::delete('/chat-channels/{id}', [ChatChannelController::class, 'destroy']);
    Route::post('/chat-channels/{id}/join', [ChatChannelController::class, 'join']);
    Route::delete('/chat-channels/{id}/leave', [ChatChannelController::class, 'leave']);
    Route::post('/chat-channels/{id}/read', [ChatChannelController::class, 'markAsRead']);

    // チャットメッセージ（認証必要）
    Route::get('/chat-channels/{channelId}/messages', [ChatMessageController::class, 'index']);
    Route::post('/chat-channels/{channelId}/messages', [ChatMessageController::class, 'store']);
    Route::delete('/chat-channels/{channelId}/messages/{messageId}', [ChatMessageController::class, 'destroy']);

    // ゲームセッション（認証必要）
    Route::post('/game-sessions', [GameSessionController::class, 'store']);
    Route::patch('/game-sessions/{id}', [GameSessionController::class, 'update']);
    Route::delete('/game-sessions/{id}', [GameSessionController::class, 'destroy']);
    Route::post('/game-sessions/{id}/join', [GameSessionController::class, 'join']);
    Route::delete('/game-sessions/{id}/leave', [GameSessionController::class, 'leave']);
    Route::put('/game-sessions/{id}/mobile-suit', [GameSessionController::class, 'selectMobileSuit']);
    Route::put('/game-sessions/{id}/pilot-point', [GameSessionController::class, 'updatePilotPoint']);
    Route::put('/game-sessions/{id}/plots/{inning}', [GameSessionController::class, 'upsertPlot']);
});

// ゲームセッション（認証不要）
Route::get('/game-sessions', [GameSessionController::class, 'index']);
Route::get('/game-sessions/{id}', [GameSessionController::class, 'show']);
Route::get('/game-sessions/{id}/report', [GameSessionController::class, 'report']);

Route::apiResource('mobile-suits', MobileSuitController::class);