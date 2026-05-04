<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ── Gateway ──────────────────────────────────────────────────────────────
// Guest  → Login page
// Auth   → Dashboard
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return Inertia::render('Authentication/Pages/Login');
})->name('home');

// Guest routes (no auth required)
require base_path('routes/web/authentication.php');

// Authenticated routes — all sub-files define their own middleware/prefix/name groups
Route::middleware(['auth'])->group(function () {
    // Dashboard
    require base_path('routes/web/dashboard.php');

    // Workflow
    require base_path('routes/web/service-orders.php');
    require base_path('routes/web/tasks.php');
    require base_path('routes/web/mini-tasks.php');
    require base_path('routes/web/work-logs.php');

    // Master Data
    require base_path('routes/web/clients.php');
    require base_path('routes/web/locations.php');
    require base_path('routes/web/materials.php');
    require base_path('routes/web/sectors.php');
    require base_path('routes/web/service-types.php');
    require base_path('routes/web/teams.php');
    require base_path('routes/web/workers.php');

    // Profile & Settings
    require base_path('routes/web/profile.php');
    require base_path('routes/web/settings.php');

    // Equipments (Loan workflow)
    require base_path('routes/web/equipments.php');

    // Exports
    require base_path('routes/web/exports.php');

    // Notifications
    require base_path('routes/web/notifications.php');

    // Analytics & Reports
    require base_path('routes/web/analytics.php');

    // Admin
    require base_path('routes/web/admin.php');
});
