<?php

use App\Features\Tasks\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [TaskController::class, 'index'])
        ->middleware('permission:tasks,view');
    Route::post('/', [TaskController::class, 'store'])
        ->middleware('permission:tasks,create');
    Route::get('/{task}', [TaskController::class, 'show'])
        ->middleware('permission:tasks,view');
    Route::put('/{task}', [TaskController::class, 'update'])
        ->middleware('permission:tasks,update');
    Route::post('/{task}/cancel', [TaskController::class, 'cancel'])
        ->middleware('permission:tasks,cancel');
    Route::post('/{task}/complete', [TaskController::class, 'complete'])
        ->middleware('permission:tasks,complete');
    Route::post('/{task}/reject', [TaskController::class, 'reject'])
        ->middleware('permission:tasks,reject');
    Route::get('/{task}/rejections', [TaskController::class, 'rejections'])
        ->middleware('permission:tasks,view');
    Route::delete('/{task}', [TaskController::class, 'destroy'])
        ->middleware('permission:tasks,delete');
});
