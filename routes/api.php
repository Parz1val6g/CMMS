<?php

use Illuminate\Support\Facades\Route;
use App\Shared\Controllers\AppSettingController;
use App\Shared\Controllers\UserPreferenceController;

// Register Authentication Routes
Route::prefix('auth')->group(base_path('app/Features/Authentication/Routes/api.php'));

// Register Workflow Routes
Route::prefix('service-orders')->group(base_path('app/Features/ServiceOrders/Routes/api.php'));
Route::prefix('tasks')->group(base_path('app/Features/Tasks/Routes/api.php'));
Route::prefix('mini-tasks')->group(base_path('app/Features/MiniTasks/Routes/api.php'));
Route::prefix('work-logs')->group(base_path('app/Features/WorkLogs/Routes/api.php'));

// Register Master Data Routes
Route::prefix('sectors')->group(base_path('app/Features/Sectors/Routes/api.php'));
Route::prefix('teams')->group(base_path('app/Features/Teams/Routes/api.php'));
Route::prefix('workers')->group(base_path('app/Features/Workers/Routes/api.php'));
Route::prefix('locations')->group(base_path('app/Features/Locations/Routes/api.php'));
Route::prefix('clients')->group(base_path('app/Features/Clients/Routes/api.php'));
Route::prefix('materials')->group(base_path('app/Features/Materials/Routes/api.php'));
Route::prefix('service-types')->group(base_path('app/Features/ServiceTypes/Routes/api.php'));
Route::prefix('attachments')->group(base_path('routes/api/attachments.php'));

// Register Notifications Routes
Route::prefix('notifications')->group(base_path('app/Features/Notifications/Routes/api.php'));

// Register Admin Routes
Route::prefix('admin')->group(base_path('app/Features/Admin/Routes/api.php'));

// Register Export Routes
Route::prefix('exports')->group(base_path('app/Features/Export/Routes/api.php'));

// Register Equipments Routes
Route::prefix('equipments')->group(base_path('app/Features/Equipments/Routes/api.php'));

// Register Equipment Types Routes
Route::prefix('equipment-types')->group(base_path('app/Features/Equipments/Routes/equipment_types_api.php'));

// Register Counting Types Routes
Route::prefix('counting-types')->group(base_path('app/Features/Equipments/Routes/counting_types_api.php'));

// Register Unit Routes
Route::prefix('units')->group(base_path('routes/api/units.php'));

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
