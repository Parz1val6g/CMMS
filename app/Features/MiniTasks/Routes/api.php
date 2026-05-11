<?php
use App\Features\MiniTasks\Controllers\Api\MiniTaskController;
use Illuminate\Support\Facades\Route;
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [MiniTaskController::class, 'index']);
    Route::post('/', [MiniTaskController::class, 'store']);
    Route::get('/{miniTask}', [MiniTaskController::class, 'show']);
    Route::put('/{miniTask}', [MiniTaskController::class, 'update']);
    Route::post('/{miniTask}/complete', [MiniTaskController::class, 'complete']);
});