<?php

use App\Features\Tickets\Controllers\Api\TicketController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/',                      [TicketController::class, 'index'])
        ->middleware('permission:tickets,view');
    Route::post('/',                     [TicketController::class, 'store'])
        ->middleware('permission:tickets,create');
    Route::get('/{ticket}',              [TicketController::class, 'show'])
        ->middleware('permission:tickets,view');
    Route::put('/{ticket}',              [TicketController::class, 'update'])
        ->middleware('permission:tickets,update');
    Route::delete('/{ticket}',           [TicketController::class, 'destroy'])
        ->middleware('permission:tickets,delete');
    Route::post('/{ticket}/convert',     [TicketController::class, 'convert'])
        ->middleware('permission:tickets,convert');
});
