<?php

use App\Http\Controllers\MobileSuitController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('mobile-suits', MobileSuitController::class);