<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\UserActionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:api')->group(function (): void {
    Route::middleware('throttle:login')->group(function (): void {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
    });

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::patch('/profile', [ProfileController::class, 'update']);
        Route::get('/profile/actions', [UserActionController::class, 'index']);
        Route::get('/files', [FileController::class, 'index']);
        Route::post('/files', [FileController::class, 'store']);
        Route::get('/files/{userFile}', [FileController::class, 'show']);
    });
});
