<?php

use Illuminate\Support\Facades\Route;
use App\Shared\Controllers\AppSettingController;
use App\Shared\Controllers\UserPreferenceController;

// Register Authentication Routes
Route::prefix('auth')->group(base_path('routes/api/authentication.php'));

// Register Workflow Routes
Route::prefix('service-orders')->group(base_path('routes/api/service-orders.php'));
Route::prefix('tasks')->group(base_path('routes/api/tasks.php'));
Route::prefix('mini-tasks')->group(base_path('routes/api/mini-tasks.php'));
Route::prefix('work-logs')->group(base_path('routes/api/work-logs.php'));

// Register Master Data Routes
Route::prefix('sectors')->group(base_path('routes/api/sectors.php'));
Route::prefix('teams')->group(base_path('routes/api/teams.php'));
Route::prefix('workers')->group(base_path('routes/api/workers.php'));
Route::prefix('locations')->group(base_path('routes/api/locations.php'));
Route::prefix('clients')->group(base_path('routes/api/clients.php'));
Route::prefix('materials')->group(base_path('routes/api/materials.php'));
Route::prefix('service-types')->group(base_path('routes/api/service-types.php'));
Route::prefix('attachments')->group(base_path('routes/api/attachments.php'));

// Register Admin Routes
Route::prefix('admin')->group(base_path('routes/api/admin.php'));

// Register Geographic Routes
Route::prefix('districts')->group(base_path('routes/api/districts.php'));
Route::prefix('municipalities')->group(base_path('routes/api/municipalities.php'));
Route::prefix('parishes')->group(base_path('routes/api/parishes.php'));

// Settings & Preferences (Secured by Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('app-settings', [AppSettingController::class, 'index']);
    Route::put('app-settings/{appSetting}', [AppSettingController::class, 'update']);
    
    Route::get('user-preferences', [UserPreferenceController::class, 'index']);
    Route::post('user-preferences', [UserPreferenceController::class, 'update']);
});
