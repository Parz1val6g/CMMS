<?php

use App\Features\Authentication\Controllers\WebAuthController;
use Illuminate\Support\Facades\Route;

// Guest-only routes
Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [WebAuthController::class, 'login']);

// Register — feature-flagged; 404 if disabled
Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register');
Route::post('/register', [WebAuthController::class, 'register']);

// Logout — requires auth, POST only, invalidates session
Route::middleware('auth')->post('/logout', [WebAuthController::class, 'logout'])->name('logout');
