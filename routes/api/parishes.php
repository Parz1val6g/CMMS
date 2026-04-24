<?php

use App\Shared\Controllers\ParishController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [ParishController::class, 'index']);
});
