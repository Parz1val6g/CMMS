<?php
use App\Features\WorkLogs\Controllers\WorkLogController;
use Illuminate\Support\Facades\Route;
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [WorkLogController::class, 'index']);
    Route::post('/', [WorkLogController::class, 'store']);
    Route::get('/{workLog}', [WorkLogController::class, 'show']);
    Route::put('/{workLog}', [WorkLogController::class, 'update']);
    Route::post('/{workLog}/complete', [WorkLogController::class, 'complete']);
});