<?php

use App\Features\Locations\Controllers\Api\LocationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [LocationController::class, 'index'])
        ->middleware('permission:locations,view');
    Route::post('/', [LocationController::class, 'store'])
        ->middleware('permission:locations,create');
    Route::get('{location}', [LocationController::class, 'show'])
        ->middleware('permission:locations,view');
    Route::put('{location}', [LocationController::class, 'update'])
        ->middleware('permission:locations,update');
    Route::delete('{location}', [LocationController::class, 'destroy'])
        ->middleware('permission:locations,delete');
});
