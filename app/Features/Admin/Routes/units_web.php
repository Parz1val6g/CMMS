<?php

use App\Features\Admin\Controllers\Web\UnitPageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('units')->name('units.')->group(function () {
    Route::get('/', [UnitPageController::class, 'index'])->name('index');
});
