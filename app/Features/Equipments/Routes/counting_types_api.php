<?php

use App\Features\Equipments\Controllers\Api\CountingTypeController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [CountingTypeController::class, 'index']);
    Route::post('/', [CountingTypeController::class, 'store']);
    Route::get('{countingType}', [CountingTypeController::class, 'show']);
    Route::put('{countingType}', [CountingTypeController::class, 'update']);
    Route::delete('{countingType}', [CountingTypeController::class, 'destroy']);
});
