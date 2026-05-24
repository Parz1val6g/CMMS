<?php

use App\Features\Equipments\Controllers\Api\EquipmentController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [EquipmentController::class, 'index'])
        ->middleware('permission:equipments,view');
    Route::post('/', [EquipmentController::class, 'store'])
        ->middleware('permission:equipments,create');
    Route::get('{equipment}', [EquipmentController::class, 'show'])
        ->middleware('permission:equipments,view');
    Route::put('{equipment}', [EquipmentController::class, 'update'])
        ->middleware('permission:equipments,update');
    Route::delete('{equipment}', [EquipmentController::class, 'destroy'])
        ->middleware('permission:equipments,delete');
});
