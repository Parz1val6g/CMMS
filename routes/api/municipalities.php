<?php

use App\Shared\Controllers\MunicipalityController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [MunicipalityController::class, 'index']);
    Route::get('/{municipality}', [MunicipalityController::class, 'show']);

    Route::post('/', [MunicipalityController::class, 'store'])
        ->middleware('permission:municipalities,create');
    Route::put('/{municipality}', [MunicipalityController::class, 'update'])
        ->middleware('permission:municipalities,update');
    Route::delete('/{municipality}', [MunicipalityController::class, 'destroy'])
        ->middleware('permission:municipalities,delete');
});