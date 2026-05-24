<?php

use App\Features\Teams\Controllers\Api\TeamController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [TeamController::class, 'index'])
        ->middleware('permission:teams,view');
    Route::post('/', [TeamController::class, 'store'])
        ->middleware('permission:teams,create');
    Route::get('{team}', [TeamController::class, 'show'])
        ->middleware('permission:teams,view');
    Route::put('{team}', [TeamController::class, 'update'])
        ->middleware('permission:teams,update');
    Route::delete('{team}', [TeamController::class, 'destroy'])
        ->middleware('permission:teams,delete');
});
