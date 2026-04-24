<?php

use App\Features\Workers\Controllers\WorkerController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [WorkerController::class, 'index']);
});
