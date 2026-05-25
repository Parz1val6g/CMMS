<?php

namespace App\Features\Dashboard\Controllers\Api;

use App\Features\Dashboard\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardService $service)
    {
        Gate::authorize('viewDashboard');

        $user       = $request->user();
        $activeRole = $request->session()->get('active_role');
        $period     = $request->query('period', 'week');

        return match ($activeRole) {
            'admin'          => $service->admin($user),
            'manager'        => $service->manager($user, $period),
            'attendant'      => $service->attendant($user, $period),
            'task_manager'   => $service->taskManager($user),
            'sector_manager' => $service->sectorManager($user),
            'team_manager'   => $service->teamManager($user),
            'worker'         => $service->worker($user),
            default          => $service->manager($user, $period),
        };
    }
}
