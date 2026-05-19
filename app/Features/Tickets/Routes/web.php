<?php

use App\Features\Tickets\Controllers\Web\TicketPageController;
use Illuminate\Support\Facades\Route;

Route::get('/tickets', [TicketPageController::class, 'index'])->name('tickets.index');
