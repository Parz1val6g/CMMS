<?php

use App\Features\ServiceTypes\Controllers\ServiceTypeController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [ServiceTypeController::class, 'index']);
});
