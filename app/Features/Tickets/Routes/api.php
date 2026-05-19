<?php

use App\Features\Tickets\Controllers\Api\TicketController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/',                      [TicketController::class, 'index']);
    Route::post('/',                     [TicketController::class, 'store']);
    Route::get('/{ticket}',              [TicketController::class, 'show']);
    Route::put('/{ticket}',              [TicketController::class, 'update']);
    Route::delete('/{ticket}',           [TicketController::class, 'destroy']);
    Route::post('/{ticket}/convert',     [TicketController::class, 'convert']);
});
