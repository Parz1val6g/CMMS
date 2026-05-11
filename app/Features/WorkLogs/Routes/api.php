<?php
use App\Features\WorkLogs\Controllers\Api\WorkLogController;
use Illuminate\Support\Facades\Route;
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [WorkLogController::class, 'index']);
    Route::post('/', [WorkLogController::class, 'store']);
    Route::get('/{workLog}', [WorkLogController::class, 'show']);
    Route::put('/{workLog}', [WorkLogController::class, 'update']);
    Route::post('/{workLog}/complete', [WorkLogController::class, 'complete']);
    Route::post('/{workLog}/approve', [WorkLogController::class, 'approve']);
    Route::post('/{workLog}/reject', [WorkLogController::class, 'reject']);
});