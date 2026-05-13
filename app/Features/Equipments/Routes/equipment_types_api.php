<?php

use App\Features\Equipments\Controllers\Api\EquipmentTypeController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [EquipmentTypeController::class, 'index']);
    Route::post('/', [EquipmentTypeController::class, 'store']);
    Route::get('{equipmentType}', [EquipmentTypeController::class, 'show']);
    Route::put('{equipmentType}', [EquipmentTypeController::class, 'update']);
    Route::delete('{equipmentType}', [EquipmentTypeController::class, 'destroy']);
});
