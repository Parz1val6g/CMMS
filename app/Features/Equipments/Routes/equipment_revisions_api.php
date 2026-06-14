<?php

use App\Features\Equipments\Controllers\Api\EquipmentRevisionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [EquipmentRevisionController::class, 'index'])
        ->middleware('permission:equipment_revisions,view');
    Route::post('/', [EquipmentRevisionController::class, 'store'])
        ->middleware('permission:equipment_revisions,create');
    Route::get('{equipmentRevision}', [EquipmentRevisionController::class, 'show'])
        ->middleware('permission:equipment_revisions,view');
    Route::put('{equipmentRevision}', [EquipmentRevisionController::class, 'update'])
        ->middleware('permission:equipment_revisions,update');
    Route::delete('{equipmentRevision}', [EquipmentRevisionController::class, 'destroy'])
        ->middleware('permission:equipment_revisions,delete');
});
