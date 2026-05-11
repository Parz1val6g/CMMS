<?php

use App\Features\Teams\Controllers\Web\TeamPageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('teams')->name('teams.')->group(function () {
    Route::get('/', [TeamPageController::class, 'index'])->name('index');
});
