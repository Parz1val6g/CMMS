<?php

use App\Features\Admin\Controllers\Web\MunicipalityPageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('admin/municipalities')->name('admin.municipalities.')->group(function () {
    Route::get('/', [MunicipalityPageController::class, 'index'])->name('index');
});
