<?php

namespace App\Features\Dashboard\Controllers;

use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\MiniTasks\Models\MiniTask;
use App\Features\Tasks\Models\Task;
use App\Features\WorkLogs\Models\WorkLog;
use App\Core\Enums\ServiceOrderStatus;
use App\Core\Enums\TaskStatus;
use App\Core\Enums\MiniTaskStatus;
use App\Core\Enums\Priority;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewDashboard');

        $kpis = [
            'active_orders' => ServiceOrder::whereIn('status', [ServiceOrderStatus::PENDING->value, ServiceOrderStatus::IN_PROGRESS->value])->count(),
            'pending_tasks' => Task::where('status', TaskStatus::PENDING->value)->count(),
            'active_mini_tasks' => MiniTask::where('status', MiniTaskStatus::IN_PROGRESS->value)->count(),
            'today_work_hours' => WorkLog::whereDate('completed_at', today())
                ->sum('duration_minutes') / 60,
        ];

        $criticalOrders = ServiceOrder::with(['location.parish'])
            ->where('priority', Priority::HIGH->value)
            ->where('status', '!=', ServiceOrderStatus::COMPLETED->value)
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($o) => [
                'id' => $o->id,
                'process' => $o->process,
                'created_at' => $o->created_at->format('d/m/Y'),
                'location' => $o->location ? [
                    'parish' => $o->location->parish ? ['name' => $o->location->parish->name] : null,
                ] : null,
            ]);

        // Fetch recent/active orders with map coordinates for the Intervention Map
        $mapOrders = ServiceOrder::with(['location.parish'])
            ->whereHas('location', fn ($q) => $q->whereNotNull('latitude')->whereNotNull('longitude'))
            ->where('status', '!=', ServiceOrderStatus::COMPLETED->value)
            ->latest()
            ->take(50)
            ->get()
            ->map(fn ($o) => [
                'id'          => $o->id,
                'process'     => $o->process,
                'priority'    => $o->priority,
                'description' => $o->location?->street_address
                    ? $o->location->street_address . ($o->location->parish ? ', ' . $o->location->parish->name : '')
                    : ($o->location?->parish?->name ?? ''),
                'latitude'    => (float) $o->location?->latitude,
                'longitude'   => (float) $o->location?->longitude,
            ]);

        return Inertia::render('Dashboard/Pages/Dashboard', [
            'kpis'              => $kpis,
            'criticalOrders'    => $criticalOrders,
            'mapOrders'         => $mapOrders,
            'googleMapsApiKey'  => config('services.google_maps.api_key'),
        ]);
    }
}
