<?php

use App\Features\Clients\Controllers\ClientController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [ClientController::class, 'index']);
    Route::post('/', [ClientController::class, 'store']);
    Route::get('{client}', [ClientController::class, 'show']);
    Route::put('{client}', [ClientController::class, 'update']);
    Route::delete('{client}', [ClientController::class, 'destroy']);
});
