<?php
use App\Features\Sectors\Controllers\SectorController;
use Illuminate\Support\Facades\Route;
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [SectorController::class, 'index']);
});