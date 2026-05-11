<?php

use App\Features\WorkLogs\Controllers\Web\WorkLogPageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('work-logs')->name('work-logs.')->group(function () {
    Route::get('/', [WorkLogPageController::class, 'index'])->name('index');
});
