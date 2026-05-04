<?php

use App\Features\Notifications\Controllers\NotificationPageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationPageController::class, 'index'])->name('index');
});
