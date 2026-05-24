<?php

use App\Features\Clients\Controllers\Api\ClientController;
use App\Features\Clients\Controllers\Api\ClientLocationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [ClientController::class, 'index'])
        ->middleware('permission:clients,view');
    Route::post('/', [ClientController::class, 'store'])
        ->middleware('permission:clients,create');
    Route::get('{client}', [ClientController::class, 'show'])
        ->middleware('permission:clients,view');
    Route::put('{client}', [ClientController::class, 'update'])
        ->middleware('permission:clients,update');
    Route::delete('{client}', [ClientController::class, 'destroy'])
        ->middleware('permission:clients,delete');

    Route::prefix('{client}/locations')->group(function () {
        Route::get('/', [ClientLocationController::class, 'index'])
            ->middleware('permission:clients,view');
        Route::post('/', [ClientLocationController::class, 'store'])
            ->middleware('permission:clients,create');
        Route::put('{clientLocation}', [ClientLocationController::class, 'update'])
            ->middleware('permission:clients,update');
        Route::delete('{clientLocation}', [ClientLocationController::class, 'destroy'])
            ->middleware('permission:clients,delete');
    });
});
