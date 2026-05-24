<?php

use App\Features\Workers\Controllers\Api\WorkerController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [WorkerController::class, 'index'])
        ->middleware('permission:workers,view');
    Route::post('/', [WorkerController::class, 'store'])
        ->middleware('permission:workers,create');
    Route::get('{worker}', [WorkerController::class, 'show'])
        ->middleware('permission:workers,view');
    Route::put('{worker}', [WorkerController::class, 'update'])
        ->middleware('permission:workers,update');
    Route::delete('{worker}', [WorkerController::class, 'destroy'])
        ->middleware('permission:workers,delete');
});
