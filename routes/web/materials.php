<?php

use App\Features\Materials\Controllers\MaterialPageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('materials')->name('materials.')->group(function () {
    Route::get('/', [MaterialPageController::class, 'index'])->name('index');
});
