<?php

use App\Features\Tasks\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [TaskController::class, 'index']);
    Route::post('/', [TaskController::class, 'store']);
    Route::get('/{task}', [TaskController::class, 'show']);
    Route::put('/{task}', [TaskController::class, 'update']);
    Route::post('/{task}/cancel', [TaskController::class, 'cancel']);
    Route::delete('/{task}', [TaskController::class, 'destroy']);
});
