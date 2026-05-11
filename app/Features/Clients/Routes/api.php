<?php

use App\Features\Clients\Controllers\Api\ClientController;
use App\Features\Clients\Controllers\Api\ClientLocationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [ClientController::class, 'index']);
    Route::post('/', [ClientController::class, 'store']);
    Route::get('{client}', [ClientController::class, 'show']);
    Route::put('{client}', [ClientController::class, 'update']);
    Route::delete('{client}', [ClientController::class, 'destroy']);

    // Nested: client locations
    Route::prefix('{client}/locations')->group(function () {
        Route::get('/', [ClientLocationController::class, 'index']);
        Route::post('/', [ClientLocationController::class, 'store']);
        Route::put('{clientLocation}', [ClientLocationController::class, 'update']);
        Route::delete('{clientLocation}', [ClientLocationController::class, 'destroy']);
    });
});
