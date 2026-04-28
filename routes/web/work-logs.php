<?php

use App\Features\WorkLogs\Controllers\WorkLogPageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('work-logs')->name('work-logs.')->group(function () {
    Route::get('/', [WorkLogPageController::class, 'index'])->name('index');
});
