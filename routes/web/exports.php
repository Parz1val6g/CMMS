<?php

use App\Features\Export\Controllers\ExportPageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('exports')->name('exports.')->group(function () {
    Route::get('/', [ExportPageController::class, 'index'])->name('index');
});
