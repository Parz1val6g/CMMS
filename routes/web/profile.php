<?php

use App\Features\Profile\Controllers\ProfilePageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('profile')->name('profile.')->group(function () {
    Route::get('/', [ProfilePageController::class, 'index'])->name('index');
});
