<?php

use App\Features\Workers\Controllers\Web\WorkerPageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('workers')->name('workers.')->group(function () {
    Route::get('/', [WorkerPageController::class, 'index'])->name('index');
});
