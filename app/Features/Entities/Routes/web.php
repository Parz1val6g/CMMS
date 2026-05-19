<?php

use App\Features\Entities\Controllers\Web\EntityPageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::prefix('entities')->name('entities.')->group(function () {
        Route::get('/', [EntityPageController::class, 'index'])->name('index');
    });

    Route::prefix('entidade')->name('entidade.')->group(function () {
        Route::get('/dashboard', [EntityPageController::class, 'dashboard'])->name('dashboard');
    });
});
