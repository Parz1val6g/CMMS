<?php

namespace App\Features\Admin\Controllers\Web;

use App\Core\Traits\GatesRoutes;
use App\Shared\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class UnitPageController extends Controller
{
    use GatesRoutes;

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Unit::class);

        $units = Unit::latest()
            ->paginate(15)
            ->through(fn($u) => [
                'id'           => $u->id,
                'name'         => $u->name,
                'abbreviation' => $u->abbreviation,
                'step'         => $u->step,
                'created_at'   => $u->created_at->format('Y-m-d'),
            ]);

        return Inertia::render('Admin/Pages/Units', [
            'units'   => $units,
            'columns' => [
                ['key' => 'name',         'label' => 'Nome',        'sortable' => true],
                ['key' => 'abbreviation', 'label' => 'Abreviatura', 'sortable' => true],
                ['key' => 'step',         'label' => 'Step'],
                ['key' => 'created_at',   'label' => 'Criado',      'sortable' => true],
            ],
            'formSchema' => [
                ['name' => 'name',         'label' => 'Nome',        'type' => 'text',   'rules' => 'sometimes|string|max:50'],
                ['name' => 'abbreviation', 'label' => 'Abreviatura', 'type' => 'text',   'rules' => 'sometimes|string|max:10'],
                ['name' => 'step',         'label' => 'Step',        'type' => 'number', 'rules' => 'sometimes|numeric|min:0.01'],
            ],
            'createFormSchema' => [
                ['name' => 'name',         'label' => 'Nome',        'type' => 'text',   'rules' => 'required|string|max:50'],
                ['name' => 'abbreviation', 'label' => 'Abreviatura', 'type' => 'text',   'rules' => 'required|string|max:10|unique:units,abbreviation'],
                ['name' => 'step',         'label' => 'Step',        'type' => 'number', 'rules' => 'sometimes|numeric|min:0.01'],
            ],
            'routes' => $this->gatedRoutes([
                'index'   => url('/api/units'),
                'store'   => url('/api/units'),
                'update'  => url('/api/units/__ID__'),
                'destroy' => url('/api/units/__ID__'),
                'show'    => url('/api/units/__ID__'),
            ], 'units'),
            'advancedFilterFields' => [
                ['value' => 'name',         'label' => 'Nome'],
                ['value' => 'abbreviation', 'label' => 'Abreviatura'],
                ['value' => 'created_at',   'label' => 'Criado'],
            ],
            'filterSchema' => [
                ['key' => 'search', 'label' => 'Pesquisa', 'type' => 'text', 'placeholder' => 'Pesquisar...'],
            ],
        ]);
    }
}
