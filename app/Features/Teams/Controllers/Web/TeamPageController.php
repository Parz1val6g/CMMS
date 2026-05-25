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

        $activeRole = $request->session()->get('active_role');

        $teams = Team::with(['sector', 'responsible'])
            ->when(
                $activeRole === 'sector_manager',
                fn($q) => $q->whereIn('sector_id', $user->headedSectors()->pluck('id'))
            )
            ->when(
                $activeRole === 'team_manager',
                fn($q) => $q->where('responsible_id', $user->id)
            )
            ->latest()
            ->paginate(15)
            ->through(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'sector' => $t->sector ? ['id' => $t->sector->id, 'name' => $t->sector->name] : null,
                'responsible' => $t->responsible ? ['id' => $t->responsible->id, 'name' => $t->responsible->first_name . ' ' . $t->responsible->last_name] : null,
                'created_at' => $t->created_at->format('Y-m-d'),
            ]);

        $createSchema = TeamFormSchema::create();
        $updateSchema = TeamFormSchema::update();

        return Inertia::render('Teams/Pages/Index', [
            'teams' => $teams,
            'columns' => [
                ['key' => 'name', 'label' => 'Nome', 'sortable' => true],
                ['key' => 'sector', 'label' => 'Setor'],
                ['key' => 'responsible', 'label' => 'Responsável'],
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
