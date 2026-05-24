<?php
use App\Features\ServiceOrders\Controllers\Api\ServiceOrderController;
use Illuminate\Support\Facades\Route;
// All these routes require the user to be logged in via Sanctum Bearer Token
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [ServiceOrderController::class, 'index'])
        ->middleware('permission:service_orders,view');
    Route::post('/', [ServiceOrderController::class, 'store'])
        ->middleware('permission:service_orders,create');
    Route::get('/{serviceOrder}', [ServiceOrderController::class, 'show'])
        ->middleware('permission:service_orders,view');
    Route::put('/{serviceOrder}', [ServiceOrderController::class, 'update'])
        ->middleware('permission:service_orders,update');
    Route::post('/{serviceOrder}/activate', [ServiceOrderController::class, 'activate'])
        ->middleware('permission:service_orders,activate');
    Route::post('/{serviceOrder}/complete', [ServiceOrderController::class, 'complete'])
        ->middleware('permission:service_orders,complete');
    Route::post('/{serviceOrder}/initiate-return', [ServiceOrderController::class, 'initiateReturn'])
        ->middleware('permission:service_orders,update');
    Route::post('/{serviceOrder}/cancel', [ServiceOrderController::class, 'cancel'])
        ->middleware('permission:service_orders,cancel');
    Route::delete('/{serviceOrder}', [ServiceOrderController::class, 'destroy'])
        ->middleware('permission:service_orders,delete');
});
