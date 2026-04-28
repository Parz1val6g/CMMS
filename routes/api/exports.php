<?php

use App\Features\Export\Controllers\ExportController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/service-orders', [ExportController::class, 'serviceOrders']);
    Route::get('/work-logs', [ExportController::class, 'workLogs']);
});
