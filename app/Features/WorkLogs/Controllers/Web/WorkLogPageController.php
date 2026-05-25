<?php

namespace App\Features\WorkLogs\Controllers\Web;

use App\Core\Enums\WorkLogStatus;
use App\Features\WorkLogs\Models\WorkLog;
use App\Features\WorkLogs\WorkLogFormSchema;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class WorkLogPageController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', WorkLog::class);

        $user = $request->user();

        $activeRole = $request->session()->get('active_role');

        $workLogs = WorkLog::with(['miniTask.task', 'workers.user', 'materials'])
            ->when(
                $activeRole === 'worker',
                fn($q) => $q->whereHas('workers', fn($wq) => $wq->where('workers.user_id', $user->id))
            )
            ->latest()
            ->paginate(15)
            ->through(fn ($wl) => [
                'id'          => $wl->id,
                'reference'   => $wl->reference,
                'description' => $wl->description,
                'duration_minutes' => $wl->duration_minutes,
                'started_at' => $wl->started_at->format('Y-m-d H:i'),
                'completed_at' => $wl->completed_at?->format('Y-m-d H:i'),
                'status' => $wl->status,
                'mini_task' => $wl->miniTask ? [
                    'id' => $wl->miniTask->id,
                    'reference' => $wl->miniTask->reference,
                    'description' => $wl->miniTask->description,
                    'task' => $wl->miniTask->task ? [
                        'reference'   => $wl->miniTask->task->reference,
                        'description' => $wl->miniTask->task->description,
                    ] : null,
                ] : null,
                'workers' => $wl->workers->map(fn ($w) => $w->user?->first_name . ' ' . $w->user?->last_name)->join(', '),
                'materials' => $wl->materials->map(fn ($m) => [
                    'name' => $m->name,
                    'quantity' => $m->pivot->quantity_used,
                ]),
            ]);

        $createSchema = WorkLogFormSchema::create();
        $updateSchema = WorkLogFormSchema::update();

        return Inertia::render('WorkLogs/Pages/Index', [
            'work_logs' => $workLogs,
            'columns' => [
                ['key' => 'reference',            'label' => 'Referência',    'sortable' => true],
                ['key' => 'mini_task.reference',  'label' => 'Mini-Tarefa', 'href' => '/mini-tasks?view={mini_task.id}'],
                ['key' => 'workers',              'label' => 'Trabalhadores'],
                ['key' => 'duration_minutes',     'label' => 'Duração (min)', 'sortable' => true],
                ['key' => 'status',               'label' => 'Estado',        'sortable' => true],
                ['key' => 'completed_at',         'label' => 'Concluído',     'sortable' => true],
            ],
            'formSchema' => $updateSchema->toArray(),
            'createFormSchema' => $createSchema->toArray(),
            'routes' => [
                'index'   => url('/api/work-logs'),
                'store'   => url('/api/work-logs'),
                'update'  => url('/api/work-logs/__ID__'),
                'destroy' => url('/api/work-logs/__ID__'),
                'show'    => url('/api/work-logs/__ID__'),
            ],
            'filterSchema' => [
                ['key' => 'search', 'label' => 'Pesquisa', 'type' => 'text', 'placeholder' => 'Pesquisar work logs...'],
                ['key' => 'status', 'label' => 'Estado',   'type' => 'select', 'options' => WorkLogStatus::options()],
            ],
            'advancedFilterFields' => [
                ['value' => 'description', 'label' => 'Descrição'],
                ['value' => 'status',      'label' => 'Estado', 'type' => 'select', 'options' => WorkLogStatus::options()],
                ['value' => 'completed_at','label' => 'Concluído'],
            ],
        ]);
    }
}
