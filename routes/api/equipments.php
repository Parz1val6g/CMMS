<?php

use App\Features\Equipments\Controllers\EquipmentController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [EquipmentController::class, 'index']);
    Route::post('/', [EquipmentController::class, 'store']);
    Route::get('{equipment}', [EquipmentController::class, 'show']);
    Route::put('{equipment}', [EquipmentController::class, 'update']);
    Route::delete('{equipment}', [EquipmentController::class, 'destroy']);
});
