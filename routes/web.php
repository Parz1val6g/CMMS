<?php

use Illuminate\Support\Facades\Route;

// For a Vue.js Single Page Application (SPA), we catch all web traffic 
// and send it to the main view so Vue Router can handle the navigation.
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');
