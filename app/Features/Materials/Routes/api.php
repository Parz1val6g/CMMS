<?php

use App\Features\Materials\Controllers\Api\MaterialController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [MaterialController::class, 'index']);
    Route::post('/', [MaterialController::class, 'store']);
    Route::get('{material}', [MaterialController::class, 'show']);
    Route::put('{material}', [MaterialController::class, 'update']);
    Route::delete('{material}', [MaterialController::class, 'destroy']);
});
