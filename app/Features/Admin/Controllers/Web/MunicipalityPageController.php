<?php

namespace App\Features\Admin\Controllers\Web;

use App\Core\Traits\GatesRoutes;
use App\Shared\Models\District;
use App\Shared\Models\Municipality;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class MunicipalityPageController extends Controller
{
    use GatesRoutes;

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Municipality::class);

        $districtOptions = District::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn($d) => ['value' => $d->id, 'label' => $d->name])
            ->values()
            ->toArray();

        $municipalities = Municipality::with('district')->orderBy('name')
            ->paginate(15)
            ->through(fn($m) => [
                'id'          => $m->id,
                'name'        => $m->name,
                'district'    => $m->district?->name,
                'district_id' => $m->district_id,
                'created_at'  => $m->created_at->format('Y-m-d'),
            ]);

        return Inertia::render('Admin/Pages/Municipalities', [
            'municipalities' => $municipalities,
            'columns' => [
                ['key' => 'name',       'label' => 'Nome',     'sortable' => true],
                ['key' => 'district',   'label' => 'Distrito'],
                ['key' => 'created_at', 'label' => 'Criado',   'sortable' => true],
            ],
            'formSchema' => [
                ['name' => 'name',        'label' => 'Nome',    'type' => 'text',   'rules' => 'sometimes|string|max:100'],
                ['name' => 'district_id', 'label' => 'Distrito','type' => 'select', 'rules' => 'sometimes|uuid|exists:districts,id', 'options' => $districtOptions],
            ],
            'createFormSchema' => [
                ['name' => 'name',        'label' => 'Nome',    'type' => 'text',   'rules' => 'required|string|max:100'],
                ['name' => 'district_id', 'label' => 'Distrito','type' => 'select', 'rules' => 'required|uuid|exists:districts,id', 'options' => $districtOptions],
            ],
            'routes' => $this->gatedRoutes([
                'index'   => url('/api/municipalities'),
                'store'   => url('/api/municipalities'),
                'update'  => url('/api/municipalities/__ID__'),
                'destroy' => url('/api/municipalities/__ID__'),
                'show'    => url('/api/municipalities/__ID__'),
            ], 'municipalities'),
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
