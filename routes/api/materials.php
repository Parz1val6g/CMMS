<?php

use App\Features\Materials\Controllers\MaterialController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [MaterialController::class, 'index']);
});
