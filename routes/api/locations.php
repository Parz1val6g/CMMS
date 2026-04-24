<?php

use App\Features\Locations\Controllers\LocationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [LocationController::class, 'index']);
});
