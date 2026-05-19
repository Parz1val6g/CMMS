<?php

use App\Features\EntityPortal\Controllers\Web\EntityPortalController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'web.access'])->prefix('portal')->name('portal.')->group(function () {
    Route::get('/', [EntityPortalController::class, 'index'])->name('index');
});
