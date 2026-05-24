<?php

use App\Features\Admin\Controllers\Api\UserController;
use App\Features\Admin\Controllers\Api\RoleController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/roles', [RoleController::class, 'index'])
        ->middleware('permission:roles,view');
    Route::post('/roles', [RoleController::class, 'store'])
        ->middleware('permission:roles,create');
    Route::get('/roles/{role}', [RoleController::class, 'show'])
        ->middleware('permission:roles,view');
    Route::put('/roles/{role}', [RoleController::class, 'update'])
        ->middleware('permission:roles,update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])
        ->middleware('permission:roles,delete');

    Route::get('/users', [UserController::class, 'index'])
        ->middleware('permission:users,view');
    Route::post('/users', [UserController::class, 'store'])
        ->middleware('permission:users,create');
    Route::get('/users/{user}', [UserController::class, 'show'])
        ->middleware('permission:users,view');
    Route::put('/users/{user}', [UserController::class, 'update'])
        ->middleware('permission:users,update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])
        ->middleware('permission:users,delete');
});
