<?php

use App\Features\MiniTasks\Controllers\MiniTaskPageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('mini-tasks')->name('mini-tasks.')->group(function () {
    Route::get('/', [MiniTaskPageController::class, 'index'])->name('index');
});
