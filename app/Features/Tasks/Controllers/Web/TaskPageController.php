<?php

namespace App\Features\Tasks\Controllers\Web;

use App\Core\Enums\TaskStatus;
use App\Features\MiniTasks\MiniTaskFormSchema;
use App\Features\Tasks\Models\Task;
use App\Features\Tasks\TaskFormSchema;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class TaskPageController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Task::class);

        $user = $request->user();
        $activeRole = $request->session()->get('active_role');

        $tasks = Task::with(['serviceOrder', 'manager', 'sectors'])
            ->when($activeRole === 'manager', fn($q) => $q->whereHas('serviceOrder', fn($sq) => $sq->where('manager_id', $user->id)))
            ->when($activeRole === 'sector_manager', fn($q) => $q->whereHas('sectors', fn($sq) => $sq->whereIn('sectors.id', $user->headedSectors()->pluck('id'))))
            ->latest()
            ->paginate(15)
            ->through(fn($t) => [
                'id'          => $t->id,
                'reference'   => $t->reference,
                'description' => $t->description,
                'status'    => $t->status,
                'created_at' => $t->created_at->format('Y-m-d'),
                'service_order' => $t->serviceOrder ? [
                    'id'      => $t->serviceOrder->id,
                    'process' => $t->serviceOrder->process,
                ] : null,
                'manager' => $t->manager ? [
                    'id'   => $t->manager->id,
                    'name' => $t->manager->first_name . ' ' . $t->manager->last_name,
                ] : null,
                'sector_id' => $t->sectors->first()?->id,
            ]);

        $createSchema = TaskFormSchema::create();
        $updateSchema = TaskFormSchema::update();

        return Inertia::render('Tasks/Pages/Index', [
            'tasks' => $tasks,
            'columns' => [
                ['key' => 'reference',     'label' => 'Referência',       'sortable' => true],
                ['key' => 'service_order', 'label' => 'Ordem de Serviço', 'href' => '/service-orders?view={service_order.id}'],
                ['key' => 'manager',       'label' => 'Gestor'],
                ['key' => 'status',        'label' => 'Estado',           'sortable' => true],
                ['key' => 'created_at',    'label' => 'Criado',           'sortable' => true],
            ],
            'formSchema' => $updateSchema->toArray(),
            'createFormSchema' => $createSchema->toArray(),
            'miniTaskCreateSchema' => MiniTaskFormSchema::create()->toArray(),
            'routes' => [
                'index' => url('/api/tasks'),
                'store' => url('/api/tasks'),
                'update' => url('/api/tasks/__ID__'),
                'destroy' => url('/api/tasks/__ID__'),
                'show' => url('/api/tasks/__ID__'),
            ],
            'advancedFilterFields' => [
                ['value' => 'description', 'label' => 'Descrição'],
                ['value' => 'status',      'label' => 'Estado', 'type' => 'select', 'options' => TaskStatus::options()],
                ['value' => 'created_at',  'label' => 'Criado'],
            ],
            'filterSchema' => [
                ['key' => 'search', 'label' => 'Pesquisa', 'type' => 'text', 'placeholder' => 'Pesquisar...'],
                ['key' => 'status', 'label' => 'Estado',   'type' => 'select', 'options' => TaskStatus::options()],
            ],
        ]);
    }
}
