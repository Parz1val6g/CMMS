<?php

use App\Features\Analytics\Controllers\AnalyticsPageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('analytics')->name('analytics.')->group(function () {
    Route::get('/', [AnalyticsPageController::class, 'index'])->name('index');
});
