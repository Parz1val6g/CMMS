<?php

use App\Features\Workers\Controllers\WorkerController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [WorkerController::class, 'index']);
    Route::post('/', [WorkerController::class, 'store']);
    Route::get('{worker}', [WorkerController::class, 'show']);
    Route::put('{worker}', [WorkerController::class, 'update']);
    Route::delete('{worker}', [WorkerController::class, 'destroy']);
});
