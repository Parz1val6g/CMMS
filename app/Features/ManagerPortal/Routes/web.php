<?php

use App\Features\ManagerPortal\Controllers\Web\ManagerPortalController;
use Illuminate\Support\Facades\Route;

Route::prefix('gestor')->name('gestor.')->group(function () {
    Route::get('/', [ManagerPortalController::class, 'index'])->name('index');
});
