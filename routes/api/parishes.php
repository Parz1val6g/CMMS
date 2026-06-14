<?php

use App\Shared\Controllers\ParishController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [ParishController::class, 'index']);
    Route::get('/{parish}', [ParishController::class, 'show']);

    Route::post('/', [ParishController::class, 'store'])
        ->middleware('permission:parishes,create');
    Route::put('/{parish}', [ParishController::class, 'update'])
        ->middleware('permission:parishes,update');
    Route::delete('/{parish}', [ParishController::class, 'destroy'])
        ->middleware('permission:parishes,delete');
});