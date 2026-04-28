<?php

use App\Features\ServiceOrders\Controllers\ServiceOrderPageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('service-orders')->name('service-orders.')->group(function () {
    Route::get('/', [ServiceOrderPageController::class, 'index'])->name('index');
});
