<?php

use App\Shared\Controllers\DistrictController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [DistrictController::class, 'index']);
    Route::get('/{district}', [DistrictController::class, 'show']);

    Route::post('/', [DistrictController::class, 'store'])
        ->middleware('permission:districts,create');
    Route::put('/{district}', [DistrictController::class, 'update'])
        ->middleware('permission:districts,update');
    Route::delete('/{district}', [DistrictController::class, 'destroy'])
        ->middleware('permission:districts,delete');
});