<?php

namespace App\Features\Admin\Controllers\Web;

use App\Core\Traits\GatesRoutes;
use App\Shared\Models\District;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class DistrictPageController extends Controller
{
    use GatesRoutes;

    public function index(Request $request)
    {
        Gate::authorize('viewAny', District::class);

        $districts = District::orderBy('name')
            ->paginate(15)
            ->through(fn($d) => [
                'id'         => $d->id,
                'name'       => $d->name,
                'created_at' => $d->created_at->format('Y-m-d'),
            ]);

        return Inertia::render('Admin/Pages/Districts', [
            'districts' => $districts,
            'columns' => [
                ['key' => 'name',       'label' => 'Nome',   'sortable' => true],
                ['key' => 'created_at', 'label' => 'Criado', 'sortable' => true],
            ],
            'formSchema'       => [['name' => 'name', 'label' => 'Nome', 'type' => 'text', 'rules' => 'sometimes|string|max:100']],
            'createFormSchema' => [['name' => 'name', 'label' => 'Nome', 'type' => 'text', 'rules' => 'required|string|max:100|unique:districts,name']],
            'routes' => $this->gatedRoutes([
                'index'   => url('/api/districts'),
                'store'   => url('/api/districts'),
                'update'  => url('/api/districts/__ID__'),
                'destroy' => url('/api/districts/__ID__'),
                'show'    => url('/api/districts/__ID__'),
            ], 'districts'),
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
