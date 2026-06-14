<?php

use App\Features\ServiceOrderCategories\Controllers\Web\ServiceOrderCategoryPageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('service-order-categories')->name('service-order-categories.')->group(function () {
    Route::get('/', [ServiceOrderCategoryPageController::class, 'index'])->name('index');
});
