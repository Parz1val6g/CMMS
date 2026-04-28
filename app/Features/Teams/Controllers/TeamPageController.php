<?php

namespace App\Features\Teams\Controllers;

use App\Features\Teams\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;

class TeamPageController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Team::class);

        $teams = Team::with(['sector'])
            ->latest()
            ->paginate(15)
            ->through(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'sector' => $t->sector ? ['id' => $t->sector->id, 'name' => $t->sector->name] : null,
                'created_at' => $t->created_at->format('Y-m-d'),
            ]);

        return Inertia::render('Teams/Pages/Index', [
            'teams' => $teams,
            'columns' => [
                ['key' => 'name', 'label' => 'Name', 'sortable' => true],
                ['key' => 'sector', 'label' => 'Sector'],
                ['key' => 'created_at', 'label' => 'Created', 'sortable' => true],
            ],
            'formSchema' => [
                ['key' => 'name', 'label' => 'Name', 'type' => 'text', 'rules' => 'required|max:100'],
                ['key' => 'sector_id', 'label' => 'Sector', 'type' => 'select', 'options' => [], 'rules' => 'required'],
            ],
            'createFormSchema' => [
                ['key' => 'name', 'label' => 'Name', 'type' => 'text', 'rules' => 'required|max:100'],
                ['key' => 'sector_id', 'label' => 'Sector', 'type' => 'select', 'options' => [], 'rules' => 'required'],
            ],
            'routes' => [
                'index' => url('/api/teams'),
                'store' => url('/api/teams'),
                'update' => url('/api/teams/__ID__'),
                'destroy' => url('/api/teams/__ID__'),
                'show' => url('/api/teams/__ID__'),
            ],
            'filterSchema' => [
                ['key' => 'search', 'label' => 'Search', 'type' => 'text', 'placeholder' => 'Search teams...'],
            ],
        ]);
    }
}
