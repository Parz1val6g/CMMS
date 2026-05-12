<?php

namespace App\Features\Workers\Controllers\Web;

use App\Features\Workers\Models\Worker;
use App\Features\Workers\WorkerFormSchema;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class WorkerPageController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Worker::class);

        $user = $request->user();

        $workers = Worker::with(['user', 'team.sector'])
            ->when(
                !$user->isAdmin() && $user->roles()->where('name', 'sector_manager')->exists(),
                fn($q) => $q->whereIn('team_id', function ($sub) use ($user) {
                    $sub->select('id')
                        ->from('teams')
                        ->whereIn('sector_id', $user->headedSectors()->pluck('id'));
                })
            )
            ->when(
                !$user->isAdmin() && $user->roles()->where('name', 'supervisor')->exists(),
                fn($q) => $q->whereIn('team_id', function ($sub) use ($user) {
                    $sub->select('team_id')
                        ->from('mini_tasks_workers_teams')
                        ->join('mini_tasks', 'mini_tasks.id', '=', 'mini_tasks_workers_teams.mini_task_id')
                        ->where('mini_tasks.supervisor_id', $user->id)
                        ->whereNotNull('mini_tasks_workers_teams.team_id')
                        ->distinct();
                })
            )
            ->latest()
            ->paginate(15)
            ->through(fn ($w) => [
                'id' => $w->id,
                'first_name' => $w->user?->first_name,
                'last_name' => $w->user?->last_name,
                'name' => $w->user?->first_name . ' ' . $w->user?->last_name,
                'email' => $w->user?->email,
                'phone' => $w->user?->phone,
                'team' => $w->team ? ['id' => $w->team->id, 'name' => $w->team->name] : null,
                'created_at' => $w->created_at->format('Y-m-d'),
            ]);

        $createSchema = WorkerFormSchema::create();
        $updateSchema = WorkerFormSchema::update();

        return Inertia::render('Workers/Pages/Index', [
            'workers' => $workers,
            'columns' => [
                ['key' => 'name', 'label' => 'Nome'],
                ['key' => 'email', 'label' => 'Email'],
                ['key' => 'phone', 'label' => 'Telefone'],
                ['key' => 'team', 'label' => 'Equipa'],
                ['key' => 'created_at', 'label' => 'Criado', 'sortable' => true],
            ],
            'formSchema' => $updateSchema->toArray(),
            'createFormSchema' => $createSchema->toArray(),
            'routes' => [
                'index' => url('/api/workers'),
                'store' => url('/api/workers'),
                'update' => url('/api/workers/__ID__'),
                'destroy' => url('/api/workers/__ID__'),
                'show' => url('/api/workers/__ID__'),
            ],
            'advancedFilterFields' => [
                ['value' => 'name',       'label' => 'Nome'],
                ['value' => 'email',      'label' => 'Email'],
                ['value' => 'phone',      'label' => 'Telefone'],
                ['value' => 'created_at', 'label' => 'Criado'],
            ],
            'filterSchema' => [
                ['key' => 'search', 'label' => 'Pesquisa', 'type' => 'text', 'placeholder' => 'Pesquisar...'],
            ],
        ]);
    }
}
