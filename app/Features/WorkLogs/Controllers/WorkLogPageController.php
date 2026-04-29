<?php

namespace App\Features\WorkLogs\Controllers;

use App\Features\WorkLogs\Models\WorkLog;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class WorkLogPageController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', WorkLog::class);

        $workLogs = WorkLog::with(['miniTask.task', 'workers.user', 'materials'])
            ->latest()
            ->paginate(15)
            ->through(fn ($wl) => [
                'id' => $wl->id,
                'description' => $wl->description,
                'duration_minutes' => $wl->duration_minutes,
                'started_at' => $wl->started_at->format('Y-m-d H:i'),
                'completed_at' => $wl->completed_at?->format('Y-m-d H:i'),
                'status' => $wl->status,
                'mini_task' => $wl->miniTask ? [
                    'id' => $wl->miniTask->id,
                    'description' => $wl->miniTask->description,
                    'task' => $wl->miniTask->task ? ['name' => $wl->miniTask->task->name] : null,
                ] : null,
                'workers' => $wl->workers->map(fn ($w) => $w->user?->first_name . ' ' . $w->user?->last_name)->join(', '),
                'materials' => $wl->materials->map(fn ($m) => [
                    'name' => $m->name,
                    'quantity' => $m->pivot->quantity_used,
                ]),
            ]);

        return Inertia::render('WorkLogs/Pages/Index', [
            'work_logs' => $workLogs,
            'columns' => [
                ['key' => 'description', 'label' => 'Description'],
                ['key' => 'mini_task', 'label' => 'Mini-Task'],
                ['key' => 'workers', 'label' => 'Workers'],
                ['key' => 'duration_minutes', 'label' => 'Duration (min)', 'sortable' => true],
                ['key' => 'status', 'label' => 'Status', 'sortable' => true],
                ['key' => 'completed_at', 'label' => 'Completed', 'sortable' => true],
            ],
            'formSchema' => [
                ['key' => 'description', 'label' => 'Description', 'type' => 'textarea', 'rules' => 'required|max:250'],
                ['key' => 'mini_task_id', 'label' => 'Mini-Task', 'type' => 'select', 'options' => [], 'rules' => 'required'],
                ['key' => 'started_at', 'label' => 'Started At', 'type' => 'datetime-local', 'rules' => 'required'],
                ['key' => 'completed_at', 'label' => 'Completed At', 'type' => 'datetime-local', 'rules' => 'required'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => [
                    ['value' => 'pending', 'label' => 'Pending'],
                    ['value' => 'approved', 'label' => 'Approved'],
                    ['value' => 'rejected', 'label' => 'Rejected'],
                ]],
            ],
            'createFormSchema' => [
                ['key' => 'description', 'label' => 'Description', 'type' => 'textarea', 'rules' => 'required|max:250'],
                ['key' => 'mini_task_id', 'label' => 'Mini-Task', 'type' => 'select', 'options' => [], 'rules' => 'required'],
                ['key' => 'started_at', 'label' => 'Started At', 'type' => 'datetime-local', 'rules' => 'required'],
                ['key' => 'completed_at', 'label' => 'Completed At', 'type' => 'datetime-local', 'rules' => 'required'],
            ],
            'routes' => [
                'index' => url('/api/work-logs'),
                'store' => url('/api/work-logs'),
                'update' => url('/api/work-logs/__ID__'),
                'destroy' => url('/api/work-logs/__ID__'),
                'show' => url('/api/work-logs/__ID__'),
            ],
            'filterSchema' => [
                ['key' => 'search', 'label' => 'Search', 'type' => 'text', 'placeholder' => 'Search work logs...'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => [
                    ['value' => 'pending', 'label' => 'Pending'],
                    ['value' => 'approved', 'label' => 'Approved'],
                    ['value' => 'rejected', 'label' => 'Rejected'],
                ]],
            ],
        ]);
    }
}
