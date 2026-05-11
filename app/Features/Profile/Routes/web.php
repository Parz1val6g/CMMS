<?php

use App\Features\Profile\Controllers\Web\ProfilePageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('profile')->name('profile.')->group(function () {
    Route::get('/', [ProfilePageController::class, 'index'])->name('index');
});
