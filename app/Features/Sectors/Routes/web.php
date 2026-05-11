<?php

use App\Features\Sectors\Controllers\Web\SectorPageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('sectors')->name('sectors.')->group(function () {
    Route::get('/', [SectorPageController::class, 'index'])->name('index');
});
