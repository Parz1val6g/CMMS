<?php

namespace App\Features\MiniTasks\Controllers\Api;

use App\Features\Equipments\Models\Equipment;
use App\Features\Materials\Models\Material;
use App\Features\MiniTasks\Models\MiniTask;
use App\Features\Teams\Models\Team;
use App\Features\Workers\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

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

        $busyWorkers = Worker::whereHas('miniTasks', fn($q) => $q->whereIn('mini_tasks.id', $overlapping))
            ->pluck('id')
            ->unique()
            ->values();

        $busyTeams = Team::whereHas('miniTasks', fn($q) => $q->whereIn('mini_tasks.id', $overlapping))
            ->pluck('id')
            ->unique()
            ->values();

        $busyEquipment = Equipment::whereHas('miniTasks', fn($q) => $q->whereIn('mini_tasks.id', $overlapping))
            ->pluck('id')
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
