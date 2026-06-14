<?php

use App\Features\ServiceOrderCategories\Controllers\Api\ServiceOrderCategoryController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [ServiceOrderCategoryController::class, 'index'])
        ->middleware('permission:service_order_categories,view');
    Route::post('/', [ServiceOrderCategoryController::class, 'store'])
        ->middleware('permission:service_order_categories,create');
    Route::get('{serviceOrderCategory}', [ServiceOrderCategoryController::class, 'show'])
        ->middleware('permission:service_order_categories,view');
    Route::put('{serviceOrderCategory}', [ServiceOrderCategoryController::class, 'update'])
        ->middleware('permission:service_order_categories,update');
    Route::delete('{serviceOrderCategory}', [ServiceOrderCategoryController::class, 'destroy'])
        ->middleware('permission:service_order_categories,delete');
});
