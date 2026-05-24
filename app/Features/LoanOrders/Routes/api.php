<?php

use App\Features\LoanOrders\Controllers\Api\LoanOrderController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [LoanOrderController::class, 'index'])
        ->middleware('permission:loan_orders,view');
    Route::post('/', [LoanOrderController::class, 'store'])
        ->middleware('permission:loan_orders,create');
    Route::get('/equipment/{equipmentId}/availability', [LoanOrderController::class, 'availability'])
        ->middleware('permission:loan_orders,view');
    Route::get('/{loanOrder}', [LoanOrderController::class, 'show'])
        ->middleware('permission:loan_orders,view');
    Route::put('/{loanOrder}', [LoanOrderController::class, 'update'])
        ->middleware('permission:loan_orders,update');
    Route::patch('/{loanOrder}', [LoanOrderController::class, 'update'])
        ->middleware('permission:loan_orders,update');
    Route::post('/{loanOrder}/approve', [LoanOrderController::class, 'approve'])
        ->middleware('permission:loan_orders,approve');
    Route::post('/{loanOrder}/checkout', [LoanOrderController::class, 'checkout'])
        ->middleware('permission:loan_orders,checkout');
    Route::post('/{loanOrder}/return', [LoanOrderController::class, 'initiateReturn'])
        ->middleware('permission:loan_orders,initiate_return');
    Route::post('/{loanOrder}/complete', [LoanOrderController::class, 'complete'])
        ->middleware('permission:loan_orders,complete');
    Route::post('/{loanOrder}/cancel', [LoanOrderController::class, 'cancel'])
        ->middleware('permission:loan_orders,cancel');
    Route::delete('/{loanOrder}', [LoanOrderController::class, 'destroy'])
        ->middleware('permission:loan_orders,delete');
});
