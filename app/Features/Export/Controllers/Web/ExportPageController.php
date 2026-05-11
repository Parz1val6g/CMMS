<?php

namespace App\Features\Export\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;

class ExportPageController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Export/Pages/Index', [
            'routes' => [
                'serviceOrders' => url('/api/exports/service-orders'),
                'workLogs'      => url('/api/exports/work-logs'),
            ],
        ]);
    }
}
