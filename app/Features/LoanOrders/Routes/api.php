<?php

use App\Features\LoanOrders\Controllers\Api\LoanOrderController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [LoanOrderController::class, 'index']);
    Route::post('/', [LoanOrderController::class, 'store']);
    Route::get('/equipment/{equipmentId}/availability', [LoanOrderController::class, 'availability']);
    Route::get('/{loanOrder}', [LoanOrderController::class, 'show']);
    Route::put('/{loanOrder}', [LoanOrderController::class, 'update']);
    Route::patch('/{loanOrder}', [LoanOrderController::class, 'update']);
    Route::post('/{loanOrder}/approve', [LoanOrderController::class, 'approve']);
    Route::post('/{loanOrder}/checkout', [LoanOrderController::class, 'checkout']);
    Route::post('/{loanOrder}/return', [LoanOrderController::class, 'initiateReturn']);
    Route::post('/{loanOrder}/complete', [LoanOrderController::class, 'complete']);
    Route::post('/{loanOrder}/cancel', [LoanOrderController::class, 'cancel']);
    Route::delete('/{loanOrder}', [LoanOrderController::class, 'destroy']);
});
