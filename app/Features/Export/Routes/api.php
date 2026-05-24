<?php

use App\Features\Export\Controllers\Api\ExportController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/service-orders', [ExportController::class, 'serviceOrders'])
        ->middleware('permission:service_orders,export');
    Route::get('/work-logs', [ExportController::class, 'workLogs'])
        ->middleware('permission:work_logs,export');
});
