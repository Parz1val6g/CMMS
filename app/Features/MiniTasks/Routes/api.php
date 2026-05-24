<?php
use App\Features\MiniTasks\Controllers\Api\MiniTaskController;
use Illuminate\Support\Facades\Route;
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [MiniTaskController::class, 'index'])
        ->middleware('permission:mini_tasks,view');
    Route::post('/', [MiniTaskController::class, 'store'])
        ->middleware('permission:mini_tasks,create');
    Route::get('/{miniTask}', [MiniTaskController::class, 'show'])
        ->middleware('permission:mini_tasks,view');
    Route::put('/{miniTask}', [MiniTaskController::class, 'update'])
        ->middleware('permission:mini_tasks,update');
    Route::post('/{miniTask}/complete', [MiniTaskController::class, 'complete'])
        ->middleware('permission:mini_tasks,complete');
});
