<?php

use App\Features\Equipments\Controllers\Web\EquipmentRevisionPageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('equipment-revisions')->name('equipment-revisions.')->group(function () {
    Route::get('/', [EquipmentRevisionPageController::class, 'index'])->name('index');
});
