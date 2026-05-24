<?php

use App\Shared\Controllers\AttachmentController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/', [AttachmentController::class, 'store'])
        ->middleware('permission:attachments,create');
    Route::delete('/{attachment}', [AttachmentController::class, 'destroy'])
        ->middleware('permission:attachments,delete');
});
