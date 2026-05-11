<?php

use App\Features\ServiceTypes\Controllers\Api\ServiceTypeController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [ServiceTypeController::class, 'index']);
    Route::post('/', [ServiceTypeController::class, 'store']);
    Route::get('{serviceType}', [ServiceTypeController::class, 'show']);
    Route::put('{serviceType}', [ServiceTypeController::class, 'update']);
    Route::delete('{serviceType}', [ServiceTypeController::class, 'destroy']);
});
