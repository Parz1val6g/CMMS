<?php

use App\Features\Equipments\Controllers\Web\EquipmentTypePageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('equipment-types')->name('equipment-types.')->group(function () {
    Route::get('/', [EquipmentTypePageController::class, 'index'])->name('index');
});
