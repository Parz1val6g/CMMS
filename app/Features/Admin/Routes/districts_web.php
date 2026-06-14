<?php

use App\Features\Admin\Controllers\Web\DistrictPageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('admin/districts')->name('admin.districts.')->group(function () {
    Route::get('/', [DistrictPageController::class, 'index'])->name('index');
});
