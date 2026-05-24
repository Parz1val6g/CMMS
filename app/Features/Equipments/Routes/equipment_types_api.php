<?php

use App\Features\Equipments\Controllers\Api\EquipmentTypeController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [EquipmentTypeController::class, 'index'])
        ->middleware('permission:equipment_types,view');
    Route::post('/', [EquipmentTypeController::class, 'store'])
        ->middleware('permission:equipment_types,create');
    Route::get('{equipmentType}', [EquipmentTypeController::class, 'show'])
        ->middleware('permission:equipment_types,view');
    Route::put('{equipmentType}', [EquipmentTypeController::class, 'update'])
        ->middleware('permission:equipment_types,update');
    Route::delete('{equipmentType}', [EquipmentTypeController::class, 'destroy'])
        ->middleware('permission:equipment_types,delete');
});
