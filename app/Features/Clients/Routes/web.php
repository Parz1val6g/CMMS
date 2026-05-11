<?php

use App\Features\Clients\Controllers\Web\ClientPageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('clients')->name('clients.')->group(function () {
    Route::get('/', [ClientPageController::class, 'index'])->name('index');
});
