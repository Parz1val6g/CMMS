<?php
use App\Features\ServiceOrders\Controllers\ServiceOrderController;
use Illuminate\Support\Facades\Route;
// All these routes require the user to be logged in via Sanctum Bearer Token
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [ServiceOrderController::class, 'index']);
    Route::post('/', [ServiceOrderController::class, 'store']);
    Route::post('/{serviceOrder}/complete', [ServiceOrderController::class, 'complete']);
    Route::get('/{serviceOrder}', [ServiceOrderController::class, 'show']);
    Route::put('/{serviceOrder}', [ServiceOrderController::class, 'update']);
    Route::post('/{serviceOrder}/cancel', [ServiceOrderController::class, 'cancel']);
});