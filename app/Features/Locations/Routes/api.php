<?php

use App\Features\Locations\Controllers\Api\LocationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [LocationController::class, 'index']);
    Route::post('/', [LocationController::class, 'store']);
    Route::get('{location}', [LocationController::class, 'show']);
    Route::put('{location}', [LocationController::class, 'update']);
    Route::delete('{location}', [LocationController::class, 'destroy']);
});
