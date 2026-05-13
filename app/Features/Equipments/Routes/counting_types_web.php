<?php

use App\Features\Equipments\Controllers\Web\CountingTypePageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('counting-types')->name('counting-types.')->group(function () {
    Route::get('/', [CountingTypePageController::class, 'index'])->name('index');
});
