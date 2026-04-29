<?php

namespace App\Features\Tasks\Controllers;

use App\Features\Tasks\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class TaskPageController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Task::class);

        $tasks = Task::with(['serviceOrder', 'manager', 'sectors'])
            ->latest()
            ->paginate(15)
            ->through(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'status' => $t->status,
                'created_at' => $t->created_at->format('Y-m-d'),
                'service_order' => $t->serviceOrder ? [
                    'id' => $t->serviceOrder->id,
                    'process' => $t->serviceOrder->process,
                ] : null,
                'manager' => $t->manager ? [
                    'id' => $t->manager->id,
                    'name' => $t->manager->first_name . ' ' . $t->manager->last_name,
                ] : null,
                'sectors' => $t->sectors->map(fn ($s) => ['id' => $s->id, 'name' => $s->name]),
            ]);

        return Inertia::render('Tasks/Pages/Index', [
            'tasks' => $tasks,
            'columns' => [
                ['key' => 'name', 'label' => 'Name', 'sortable' => true],
                ['key' => 'service_order', 'label' => 'Service Order'],
                ['key' => 'manager', 'label' => 'Manager'],
                ['key' => 'status', 'label' => 'Status', 'sortable' => true],
                ['key' => 'created_at', 'label' => 'Created', 'sortable' => true],
            ],
            'formSchema' => [
                ['key' => 'name', 'label' => 'Name', 'type' => 'text', 'rules' => 'required|max:150'],
                ['key' => 'service_order_id', 'label' => 'Service Order', 'type' => 'select', 'options' => [], 'rules' => 'required'],
                ['key' => 'sector_id', 'label' => 'Sector', 'type' => 'select', 'options' => []],
                ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => [
                    ['value' => 'pending', 'label' => 'Pending'],
                    ['value' => 'in_progress', 'label' => 'In Progress'],
                    ['value' => 'completed', 'label' => 'Completed'],
                    ['value' => 'cancelled', 'label' => 'Cancelled'],
                ]],
            ],
            'createFormSchema' => [
                ['key' => 'name', 'label' => 'Name', 'type' => 'text', 'rules' => 'required|max:150'],
                ['key' => 'service_order_id', 'label' => 'Service Order', 'type' => 'select', 'options' => [], 'rules' => 'required'],
                ['key' => 'sector_id', 'label' => 'Sector', 'type' => 'select', 'options' => [], 'rules' => 'required'],
            ],
            'routes' => [
                'index' => url('/api/tasks'),
                'store' => url('/api/tasks'),
                'update' => url('/api/tasks/__ID__'),
                'destroy' => url('/api/tasks/__ID__'),
                'show' => url('/api/tasks/__ID__'),
            ],
            'filterSchema' => [
                ['key' => 'search', 'label' => 'Search', 'type' => 'text', 'placeholder' => 'Search tasks...'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => [
                    ['value' => 'pending', 'label' => 'Pending'],
                    ['value' => 'in_progress', 'label' => 'In Progress'],
                    ['value' => 'completed', 'label' => 'Completed'],
                ]],
            ],
        ]);
    }
}
