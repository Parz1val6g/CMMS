<?php

use App\Features\Settings\Controllers\SettingsPageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('settings')->name('settings.')->group(function () {
    Route::get('/', [SettingsPageController::class, 'index'])->name('index');

    // Profile & Security actions
    Route::post('/update-user', [SettingsPageController::class, 'updateUser'])->name('update-user');
    Route::post('/update-admin', [SettingsPageController::class, 'updateAdmin'])->name('update-admin');
    Route::post('/update-password', [SettingsPageController::class, 'updatePassword'])->name('update-password');
    Route::post('/delete-account', [SettingsPageController::class, 'deleteAccount'])->name('delete-account');
});
