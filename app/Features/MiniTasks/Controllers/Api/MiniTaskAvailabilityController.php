<?php

namespace App\Features\MiniTasks\Controllers\Api;

use App\Features\Materials\Models\Material;
use App\Features\MiniTasks\Models\MiniTask;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class MiniTaskAvailabilityController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $start = $request->input('start_date');
        $end   = $request->input('end_date');

        // Mini-tasks whose date range overlaps [start, end]
        $overlapping = MiniTask::where('start_date', '<=', $end)
            ->where('end_date', '>=', $start)
            ->pluck('id');

        $busyWorkers = DB::table('mini_tasks_workers_teams')
            ->whereIn('mini_task_id', $overlapping)
            ->whereNotNull('worker_id')
            ->pluck('worker_id')
            ->unique()
            ->values();

        $busyTeams = DB::table('mini_tasks_workers_teams')
            ->whereIn('mini_task_id', $overlapping)
            ->whereNotNull('team_id')
            ->pluck('team_id')
            ->unique()
            ->values();

        $busyEquipment = DB::table('mini_task_equipment')
            ->whereIn('mini_task_id', $overlapping)
            ->pluck('equipment_id')
            ->unique()
            ->values();

        $materialStock = Material::all(['id', 'stock_quantity'])
            ->mapWithKeys(fn($m) => [$m->id => (float) $m->stock_quantity]);

        return response()->json([
            'busy_worker_ids'    => $busyWorkers,
            'busy_team_ids'      => $busyTeams,
            'busy_equipment_ids' => $busyEquipment,
            'material_stock'     => $materialStock,
        ]);
    }
}
