<?php

use App\Features\LoanOrders\Controllers\Web\LoanOrderPageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('loan-orders')->name('loan-orders.')->group(function () {
    Route::get('/', [LoanOrderPageController::class, 'index'])->name('index');
});
