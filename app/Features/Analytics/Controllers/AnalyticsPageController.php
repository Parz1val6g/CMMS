<?php

namespace App\Features\Analytics\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;

class AnalyticsPageController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Analytics/Pages/Index');
    }
}
