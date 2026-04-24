<?php

use App\Features\Clients\Controllers\ClientController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [ClientController::class, 'index']);
});
