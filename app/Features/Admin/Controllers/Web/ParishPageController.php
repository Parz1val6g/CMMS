<?php

namespace App\Features\Admin\Controllers\Web;

use App\Core\Traits\GatesRoutes;
use App\Shared\Models\Municipality;
use App\Shared\Models\Parish;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ParishPageController extends Controller
{
    use GatesRoutes;

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Parish::class);

        $municipalityOptions = Municipality::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn($m) => ['value' => $m->id, 'label' => $m->name])
            ->values()
            ->toArray();

        $parishes = Parish::with('municipality.district')->orderBy('name')
            ->paginate(15)
            ->through(fn($p) => [
                'id'              => $p->id,
                'name'            => $p->name,
                'municipality'    => $p->municipality?->name,
                'municipality_id' => $p->municipality_id,
                'district'        => $p->municipality?->district?->name,
                'created_at'      => $p->created_at->format('Y-m-d'),
            ]);

        return Inertia::render('Admin/Pages/Parishes', [
            'parishes' => $parishes,
            'columns' => [
                ['key' => 'name',         'label' => 'Freguesia',  'sortable' => true],
                ['key' => 'municipality', 'label' => 'Municipio'],
                ['key' => 'district',     'label' => 'Distrito'],
                ['key' => 'created_at',   'label' => 'Criado',     'sortable' => true],
            ],
            'formSchema' => [
                ['name' => 'name',            'label' => 'Nome',      'type' => 'text',   'rules' => 'sometimes|string|max:100'],
                ['name' => 'municipality_id', 'label' => 'Município', 'type' => 'select', 'rules' => 'sometimes|uuid|exists:municipalities,id', 'options' => $municipalityOptions],
            ],
            'createFormSchema' => [
                ['name' => 'name',            'label' => 'Nome',      'type' => 'text',   'rules' => 'required|string|max:100'],
                ['name' => 'municipality_id', 'label' => 'Município', 'type' => 'select', 'rules' => 'required|uuid|exists:municipalities,id', 'options' => $municipalityOptions],
            ],
            'routes' => $this->gatedRoutes([
                'index'   => url('/api/parishes'),
                'store'   => url('/api/parishes'),
                'update'  => url('/api/parishes/__ID__'),
                'destroy' => url('/api/parishes/__ID__'),
                'show'    => url('/api/parishes/__ID__'),
            ], 'parishes'),
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
