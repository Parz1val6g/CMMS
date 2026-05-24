<?php
use App\Features\WorkLogs\Controllers\Api\WorkLogController;
use Illuminate\Support\Facades\Route;
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [WorkLogController::class, 'index'])
        ->middleware('permission:work_logs,view');
    Route::post('/', [WorkLogController::class, 'store'])
        ->middleware('permission:work_logs,create');
    Route::get('/{workLog}', [WorkLogController::class, 'show'])
        ->middleware('permission:work_logs,view');
    Route::put('/{workLog}', [WorkLogController::class, 'update'])
        ->middleware('permission:work_logs,update');
    Route::post('/{workLog}/complete', [WorkLogController::class, 'complete'])
        ->middleware('permission:work_logs,complete');
    Route::post('/{workLog}/approve', [WorkLogController::class, 'approve'])
        ->middleware('permission:work_logs,approve');
    Route::post('/{workLog}/reject', [WorkLogController::class, 'reject'])
        ->middleware('permission:work_logs,reject');
});
