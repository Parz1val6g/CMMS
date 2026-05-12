<?php

namespace App\Features\MiniTasks\Controllers\Web;

use App\Core\Enums\MiniTaskStatus;
use App\Features\MiniTasks\Models\MiniTask;
use App\Features\MiniTasks\MiniTaskFormSchema;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class MiniTaskPageController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', MiniTask::class);

        $user = $request->user();

        $miniTasks = MiniTask::with(['task.serviceOrder', 'supervisor', 'workers.user', 'teams'])
            ->when(
                !$user->isAdmin() && $user->roles()->where('name', 'supervisor')->exists(),
                fn($q) => $q->where('supervisor_id', $user->id)
            )
            ->when(
                !$user->isAdmin() && $user->roles()->where('name', 'sector_manager')->exists(),
                fn($q) => $q->whereHas('task.sectors', fn($sq) => $sq->whereIn('sectors.id', $user->headedSectors()->pluck('id')))
            )
            ->latest()
            ->paginate(15)
            ->through(fn ($mt) => [
                'id'          => $mt->id,
                'reference'   => $mt->reference,
                'description' => $mt->description,
                'status'      => $mt->status,
                'created_at'  => $mt->created_at->format('Y-m-d'),
                'task' => $mt->task ? [
                    'id'          => $mt->task->id,
                    'reference'   => $mt->task->reference,
                    'description' => $mt->task->description,
                    'service_order' => $mt->task->serviceOrder
                        ? ['process' => $mt->task->serviceOrder->process]
                        : null,
                ] : null,
                'supervisor' => $mt->supervisor ? [
                    'id'   => $mt->supervisor->id,
                    'name' => $mt->supervisor->first_name . ' ' . $mt->supervisor->last_name,
                ] : null,
                'workers_list' => $mt->workers->pluck('user.first_name')->join(', '),
                'teams_list'   => $mt->teams->pluck('name')->join(', '),
                'workers'      => $mt->workers->map(fn ($w) => ['id' => $w->id]),
                'teams'        => $mt->teams->map(fn ($t) => ['id' => $t->id]),
            ]);

        $createSchema = MiniTaskFormSchema::create();
        $updateSchema = MiniTaskFormSchema::update();

        return Inertia::render('MiniTasks/Pages/Index', [
            'mini_tasks' => $miniTasks,
            'columns' => [
                ['key' => 'reference',      'label' => 'Referência',   'sortable' => true],
                ['key' => 'task.reference', 'label' => 'Tarefa', 'href' => '/tasks?view={task.id}'],
                ['key' => 'supervisor',     'label' => 'Supervisor'],
                ['key' => 'workers_list',   'label' => 'Trabalhadores'],
                ['key' => 'teams_list',     'label' => 'Equipas'],
                ['key' => 'status',         'label' => 'Estado',       'sortable' => true],
                ['key' => 'created_at',     'label' => 'Criado',       'sortable' => true],
            ],
            'formSchema'       => $updateSchema->toArray(),
            'createFormSchema' => $createSchema->toArray(),
            'routes' => [
                'index'  => url('/api/mini-tasks'),
                'store'  => url('/api/mini-tasks'),
                'update' => url('/api/mini-tasks/__ID__'),
                'show'   => url('/api/mini-tasks/__ID__'),
            ],
            'filterSchema' => [
                ['key' => 'search', 'label' => 'Pesquisa', 'type' => 'text', 'placeholder' => 'Pesquisar mini-tarefas...'],
                ['key' => 'status', 'label' => 'Estado',   'type' => 'select', 'options' => [
                    ['value' => 'pending',     'label' => 'Pendente'],
                    ['value' => 'in_progress', 'label' => 'Em Progresso'],
                    ['value' => 'completed',   'label' => 'Concluído'],
                ]],
            ],
            'advancedFilterFields' => [
                ['value' => 'description', 'label' => 'Descrição'],
                ['value' => 'status',      'label' => 'Estado', 'type' => 'select', 'options' => MiniTaskStatus::options()],
                ['value' => 'created_at',  'label' => 'Criado'],
            ],
        ]);
    }
}
