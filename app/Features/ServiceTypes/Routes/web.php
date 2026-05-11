<?php

use App\Features\ServiceTypes\Controllers\Web\ServiceTypePageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('service-types')->name('service-types.')->group(function () {
    Route::get('/', [ServiceTypePageController::class, 'index'])->name('index');
});
