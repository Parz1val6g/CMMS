<?php

use App\Features\Teams\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [TeamController::class, 'index']);
});
