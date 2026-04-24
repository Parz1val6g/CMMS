<?php
use App\Features\Authentication\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
Route::post('/login', [AuthController::class, 'login']);
// Protect these routes using Sanctum (requires a valid Bearer Token)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});