<?php

use App\Features\Authentication\Controllers\WebAuthController;
use Illuminate\Support\Facades\Route;

// Guest-only routes
Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [WebAuthController::class, 'login']);

// Register — only available when the feature flag is enabled
if (config('features.registration_enabled')) {
    Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [WebAuthController::class, 'register']);
}

// Logout — requires auth, POST only, invalidates session
Route::middleware('auth')->post('/logout', [WebAuthController::class, 'logout'])->name('logout');
