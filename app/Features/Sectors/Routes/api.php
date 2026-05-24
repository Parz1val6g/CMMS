<?php

use App\Features\Sectors\Controllers\Api\SectorController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [SectorController::class, 'index'])
        ->middleware('permission:sectors,view');
    Route::post('/', [SectorController::class, 'store'])
        ->middleware('permission:sectors,create');
    Route::get('{sector}', [SectorController::class, 'show'])
        ->middleware('permission:sectors,view');
    Route::put('{sector}', [SectorController::class, 'update'])
        ->middleware('permission:sectors,update');
    Route::delete('{sector}', [SectorController::class, 'destroy'])
        ->middleware('permission:sectors,delete');
});
