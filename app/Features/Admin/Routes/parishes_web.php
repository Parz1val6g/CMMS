<?php

use App\Features\Admin\Controllers\Web\ParishPageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('admin/parishes')->name('admin.parishes.')->group(function () {
    Route::get('/', [ParishPageController::class, 'index'])->name('index');
});
