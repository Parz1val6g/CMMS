<?php

use App\Features\Tasks\Controllers\Web\TaskPageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('tasks')->name('tasks.')->group(function () {
    Route::get('/', [TaskPageController::class, 'index'])->name('index');
});
