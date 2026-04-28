<?php

namespace App\Features\MiniTasks\Controllers;

use App\Features\MiniTasks\Models\MiniTask;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;

class MiniTaskPageController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', MiniTask::class);

        $miniTasks = MiniTask::with(['task.serviceOrder', 'supervisor', 'workers', 'teams'])
            ->latest()
            ->paginate(15)
            ->through(fn ($mt) => [
                'id' => $mt->id,
                'description' => $mt->description,
                'status' => $mt->status,
                'created_at' => $mt->created_at->format('Y-m-d'),
                'task' => $mt->task ? [
                    'id' => $mt->task->id,
                    'name' => $mt->task->name,
                    'service_order' => $mt->task->serviceOrder ? ['process' => $mt->task->serviceOrder->process] : null,
                ] : null,
                'supervisor' => $mt->supervisor ? [
                    'id' => $mt->supervisor->id,
                    'name' => $mt->supervisor->first_name . ' ' . $mt->supervisor->last_name,
                ] : null,
                'assigned_workers' => $mt->workers->pluck('user.first_name')->join(', '),
                'assigned_teams' => $mt->teams->pluck('name')->join(', '),
            ]);

        return Inertia::render('MiniTasks/Pages/Index', [
            'mini_tasks' => $miniTasks,
            'columns' => [
                ['key' => 'description', 'label' => 'Description'],
                ['key' => 'task', 'label' => 'Task'],
                ['key' => 'supervisor', 'label' => 'Supervisor'],
                ['key' => 'assigned_workers', 'label' => 'Workers'],
                ['key' => 'assigned_teams', 'label' => 'Teams'],
                ['key' => 'status', 'label' => 'Status', 'sortable' => true],
                ['key' => 'created_at', 'label' => 'Created', 'sortable' => true],
            ],
            'formSchema' => [
                ['key' => 'description', 'label' => 'Description', 'type' => 'text', 'rules' => 'required|max:250'],
                ['key' => 'task_id', 'label' => 'Task', 'type' => 'select', 'options' => [], 'rules' => 'required'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => [
                    ['value' => 'pending', 'label' => 'Pending'],
                    ['value' => 'in_progress', 'label' => 'In Progress'],
                    ['value' => 'completed', 'label' => 'Completed'],
                ]],
            ],
            'createFormSchema' => [
                ['key' => 'description', 'label' => 'Description', 'type' => 'text', 'rules' => 'required|max:250'],
                ['key' => 'task_id', 'label' => 'Task', 'type' => 'select', 'options' => [], 'rules' => 'required'],
                ['key' => 'worker_id', 'label' => 'Worker', 'type' => 'select', 'options' => []],
                ['key' => 'team_id', 'label' => 'Team', 'type' => 'select', 'options' => []],
            ],
            'routes' => [
                'index' => url('/api/mini-tasks'),
                'store' => url('/api/mini-tasks'),
                'update' => url('/api/mini-tasks/__ID__'),
                'show' => url('/api/mini-tasks/__ID__'),
            ],
            'filterSchema' => [
                ['key' => 'search', 'label' => 'Search', 'type' => 'text', 'placeholder' => 'Search mini-tasks...'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => [
                    ['value' => 'pending', 'label' => 'Pending'],
                    ['value' => 'in_progress', 'label' => 'In Progress'],
                    ['value' => 'completed', 'label' => 'Completed'],
                ]],
            ],
        ]);
    }
}
