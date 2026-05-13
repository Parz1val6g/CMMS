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
    if (auth()->check())
        return redirect()->route('dashboard');
    return Inertia::render('Authentication/Pages/Login');
})->name('home');

// Guest routes (no auth required)
require base_path('app/Features/Authentication/Routes/web.php');

// Authenticated routes — all sub-files define their own middleware/prefix/name groups
Route::middleware(['auth', 'web.access'])->group(function () {
    // Dashboard
    require base_path('app/Features/Dashboard/Routes/web.php');

    // Workflow
    require base_path('app/Features/ServiceOrders/Routes/web.php');
    require base_path('app/Features/Tasks/Routes/web.php');
    require base_path('app/Features/MiniTasks/Routes/web.php');
    require base_path('app/Features/WorkLogs/Routes/web.php');

    // Master Data
    require base_path('app/Features/Clients/Routes/web.php');
    require base_path('app/Features/Locations/Routes/web.php');
    require base_path('app/Features/Materials/Routes/web.php');
    require base_path('app/Features/Sectors/Routes/web.php');
    require base_path('app/Features/ServiceTypes/Routes/web.php');
    require base_path('app/Features/Teams/Routes/web.php');
    require base_path('app/Features/Workers/Routes/web.php');

    // Profile & Settings
    require base_path('app/Features/Profile/Routes/web.php');
    require base_path('app/Features/Settings/Routes/web.php');

    // Equipments (Loan workflow)
    require base_path('app/Features/Equipments/Routes/web.php');

    // Equipment Types
    require base_path('app/Features/Equipments/Routes/equipment_types_web.php');

    // Counting Types
    require base_path('app/Features/Equipments/Routes/counting_types_web.php');

    // Exports
    require base_path('app/Features/Export/Routes/web.php');

    // Notifications
    require base_path('app/Features/Notifications/Routes/web.php');

    // Analytics & Reports
    require base_path('app/Features/Analytics/Routes/web.php');

    // Admin
    require base_path('app/Features/Admin/Routes/web.php');
});
