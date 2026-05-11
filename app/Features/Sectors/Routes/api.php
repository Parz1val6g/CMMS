<?php

use App\Features\Sectors\Controllers\Api\SectorController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [SectorController::class, 'index']);
    Route::post('/', [SectorController::class, 'store']);
    Route::get('{sector}', [SectorController::class, 'show']);
    Route::put('{sector}', [SectorController::class, 'update']);
    Route::delete('{sector}', [SectorController::class, 'destroy']);
});