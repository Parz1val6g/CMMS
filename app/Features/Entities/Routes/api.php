<?php

use App\Features\Entities\Controllers\Api\EntityController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [EntityController::class, 'index'])
        ->middleware('permission:entities,view');
    Route::post('/', [EntityController::class, 'store'])
        ->middleware('permission:entities,create');
    Route::get('/{entity}', [EntityController::class, 'show'])
        ->middleware('permission:entities,view');
    Route::put('/{entity}', [EntityController::class, 'update'])
        ->middleware('permission:entities,update');
    Route::patch('/{entity}', [EntityController::class, 'update'])
        ->middleware('permission:entities,update');
    Route::delete('/{entity}', [EntityController::class, 'destroy'])
        ->middleware('permission:entities,delete');
});
