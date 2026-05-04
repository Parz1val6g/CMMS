<?php

use App\Features\Equipments\Controllers\EquipmentController;
use App\Features\Equipments\Controllers\EquipmentPageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('equipments')->name('equipments.')->group(function () {
    // Page (Inertia)
    Route::get('/', [EquipmentPageController::class, 'index'])->name('index');

    // CRUD (JSON) — web middleware includes VerifyCsrfToken
    Route::post('/', [EquipmentController::class, 'store'])->name('store');
    Route::get('{equipment}', [EquipmentController::class, 'show'])->name('show');
    Route::put('{equipment}', [EquipmentController::class, 'update'])->name('update');
    Route::delete('{equipment}', [EquipmentController::class, 'destroy'])->name('destroy');
});
