<?php
use App\Features\Notifications\Controllers\Api\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [NotificationController::class, 'index']);
    Route::patch('{notification}/read', [NotificationController::class, 'markAsRead']);
});
