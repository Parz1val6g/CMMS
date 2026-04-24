<?php

use App\Shared\Controllers\DistrictController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [DistrictController::class, 'index']);
});
