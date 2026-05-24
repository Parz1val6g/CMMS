<?php

use App\Features\Materials\Controllers\Api\MaterialController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [MaterialController::class, 'index'])
        ->middleware('permission:materials,view');
    Route::post('/', [MaterialController::class, 'store'])
        ->middleware('permission:materials,create');
    Route::get('{material}', [MaterialController::class, 'show'])
        ->middleware('permission:materials,view');
    Route::put('{material}', [MaterialController::class, 'update'])
        ->middleware('permission:materials,update');
    Route::delete('{material}', [MaterialController::class, 'destroy'])
        ->middleware('permission:materials,delete');
});
