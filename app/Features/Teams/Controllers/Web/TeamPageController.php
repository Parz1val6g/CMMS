<?php

namespace App\Features\Teams\Controllers\Web;

use App\Features\Teams\Models\Team;
use App\Features\Teams\TeamFormSchema;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class TeamPageController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Team::class);

        $user = $request->user();

        $teams = Team::with(['sector'])
            ->when(
                !$user->isAdmin() && $user->roles()->where('name', 'sector_manager')->exists(),
                fn($q) => $q->whereIn('sector_id', $user->headedSectors()->pluck('id'))
            )
            ->when(
                !$user->isAdmin() && $user->roles()->where('name', 'supervisor')->exists(),
                fn($q) => $q->whereIn('id', function ($sub) use ($user) {
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
            ->through(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'sector' => $t->sector ? ['id' => $t->sector->id, 'name' => $t->sector->name] : null,
                'created_at' => $t->created_at->format('Y-m-d'),
            ]);

        $createSchema = TeamFormSchema::create();
        $updateSchema = TeamFormSchema::update();

        return Inertia::render('Teams/Pages/Index', [
            'teams' => $teams,
            'columns' => [
                ['key' => 'name', 'label' => 'Nome', 'sortable' => true],
                ['key' => 'sector', 'label' => 'Setor'],
                ['key' => 'created_at', 'label' => 'Criado', 'sortable' => true],
            ],
            'formSchema' => $updateSchema->toArray(),
            'createFormSchema' => $createSchema->toArray(),
            'routes' => [
                'index' => url('/api/teams'),
                'store' => url('/api/teams'),
                'update' => url('/api/teams/__ID__'),
                'destroy' => url('/api/teams/__ID__'),
                'show' => url('/api/teams/__ID__'),
            ],
            'advancedFilterFields' => [
                ['value' => 'name',       'label' => 'Nome'],
                ['value' => 'created_at', 'label' => 'Criado'],
            ],
            'filterSchema' => [
                ['key' => 'search', 'label' => 'Pesquisa', 'type' => 'text', 'placeholder' => 'Pesquisar...'],
            ],
        ]);
    }
}
