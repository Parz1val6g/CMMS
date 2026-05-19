<?php

use App\Features\Entities\Controllers\Api\EntityController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [EntityController::class, 'index']);
    Route::post('/', [EntityController::class, 'store']);
    Route::get('/{entity}', [EntityController::class, 'show']);
    Route::put('/{entity}', [EntityController::class, 'update']);
    Route::patch('/{entity}', [EntityController::class, 'update']);
    Route::delete('/{entity}', [EntityController::class, 'destroy']);
});
