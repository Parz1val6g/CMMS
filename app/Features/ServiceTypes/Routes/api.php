<?php

use App\Features\ServiceTypes\Controllers\Api\ServiceTypeController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [ServiceTypeController::class, 'index'])
        ->middleware('permission:service_types,view');
    Route::post('/', [ServiceTypeController::class, 'store'])
        ->middleware('permission:service_types,create');
    Route::get('{serviceType}', [ServiceTypeController::class, 'show'])
        ->middleware('permission:service_types,view');
    Route::put('{serviceType}', [ServiceTypeController::class, 'update'])
        ->middleware('permission:service_types,update');
    Route::delete('{serviceType}', [ServiceTypeController::class, 'destroy'])
        ->middleware('permission:service_types,delete');
});
