<?php

use App\Features\Authentication\Controllers\Web\WebAuthController;
use Illuminate\Support\Facades\Route;

// Guest-only routes — redirect logged-in users to dashboard
Route::middleware('guest')->get('/login', [WebAuthController::class, 'showLogin'])->name('login');
Route::middleware('guest')->post('/login', [WebAuthController::class, 'login']);

// Register — only available when the feature flag is enabled
if (config('features.registration_enabled')) {
    Route::middleware('guest')->get('/register', [WebAuthController::class, 'showRegister'])->name('register');
    Route::middleware('guest')->post('/register', [WebAuthController::class, 'register']);
}

// Logout â€” requires auth, POST only, invalidates session
Route::middleware('auth')->post('/logout', [WebAuthController::class, 'logout'])->name('logout');
