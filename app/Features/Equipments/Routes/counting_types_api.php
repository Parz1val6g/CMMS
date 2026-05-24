<?php

use App\Features\Equipments\Controllers\Api\CountingTypeController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [CountingTypeController::class, 'index'])
        ->middleware('permission:counting_types,view');
    Route::post('/', [CountingTypeController::class, 'store'])
        ->middleware('permission:counting_types,create');
    Route::get('{countingType}', [CountingTypeController::class, 'show'])
        ->middleware('permission:counting_types,view');
    Route::put('{countingType}', [CountingTypeController::class, 'update'])
        ->middleware('permission:counting_types,update');
    Route::delete('{countingType}', [CountingTypeController::class, 'destroy'])
        ->middleware('permission:counting_types,delete');
});
