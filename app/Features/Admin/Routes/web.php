<?php

use App\Features\Admin\Controllers\Web\AdminPageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminPageController::class, 'index'])->name('index');
    Route::get('/users', [AdminPageController::class, 'users'])->name('users');
    Route::get('/series', [AdminPageController::class, 'series'])->name('series');
});
