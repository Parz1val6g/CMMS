<?php

use App\Features\Teams\Controllers\Api\TeamController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [TeamController::class, 'index']);
    Route::post('/', [TeamController::class, 'store']);
    Route::get('{team}', [TeamController::class, 'show']);
    Route::put('{team}', [TeamController::class, 'update']);
    Route::delete('{team}', [TeamController::class, 'destroy']);
});
