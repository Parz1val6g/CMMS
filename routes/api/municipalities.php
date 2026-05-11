<?php

use App\Shared\Controllers\MunicipalityController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [MunicipalityController::class, 'index']);
    Route::get('/{municipality}', [MunicipalityController::class, 'show']);
});