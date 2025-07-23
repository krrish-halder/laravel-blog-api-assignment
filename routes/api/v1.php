<?php

use App\Http\Controllers\API\V1\AuthController;
use App\Http\Controllers\API\V1\BlogController;
use App\Http\Controllers\API\V1\LikeController;
use Illuminate\Support\Facades\Route;

Route::get('test', function () {
    return response()->json(['message' => 'API is working']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);


Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('blogs', BlogController::class);

    Route::post('/blogs/{blog}/like-toggle', [LikeController::class, 'toggle']);
});
