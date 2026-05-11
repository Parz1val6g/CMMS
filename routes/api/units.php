<?php

use App\Shared\Controllers\UnitController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [UnitController::class, 'index']);
    Route::post('/', [UnitController::class, 'store']);
    Route::get('/{unit}', [UnitController::class, 'show']);
    Route::put('/{unit}', [UnitController::class, 'update']);
    Route::delete('/{unit}', [UnitController::class, 'destroy']);
});