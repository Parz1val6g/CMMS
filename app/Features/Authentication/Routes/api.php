<?php

use App\Features\Authentication\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

// Public routes (no auth required) — WITH RATE LIMITING
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');  // 5 attempts per 1 minute

Route::post('/password-reset', [AuthController::class, 'passwordReset'])
    ->middleware('throttle:3,60');  // 3 attempts per 60 minutes

// Protected routes (require auth)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/switch-role', [AuthController::class, 'switchRole']);
});