<?php

use App\Features\Locations\Controllers\LocationPageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('locations')->name('locations.')->group(function () {
    Route::get('/', [LocationPageController::class, 'index'])->name('index');
});
