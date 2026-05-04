<?php

namespace App\Features\Materials\Controllers;

use App\Features\Materials\Models\Material;
use App\Features\Materials\Schemas\MaterialFormSchema;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class MaterialPageController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Material::class);

        $materials = Material::with(['unit'])
            ->latest()
            ->paginate(15)
            ->through(fn ($m) => [
                'id' => $m->id,
                'name' => $m->name,
                'stock_quantity' => $m->stock_quantity,
                'unit' => $m->unit ? [
                    'id' => $m->unit->id,
                    'name' => $m->unit->name,
                    'abbreviation' => $m->unit->abbreviation,
                ] : null,
                'created_at' => $m->created_at->format('Y-m-d'),
            ]);

        $createSchema = MaterialFormSchema::create();
        $updateSchema = MaterialFormSchema::update();

        return Inertia::render('Materials/Pages/Index', [
            'materials' => $materials,
            'columns' => [
                ['key' => 'name', 'label' => 'Name', 'sortable' => true],
                ['key' => 'unit', 'label' => 'Unit'],
                ['key' => 'stock_quantity', 'label' => 'Stock', 'sortable' => true],
                ['key' => 'created_at', 'label' => 'Created', 'sortable' => true],
            ],
            'formSchema' => $updateSchema->toArray(),
            'createFormSchema' => $createSchema->toArray(),
            'routes' => [
                'index' => url('/api/materials'),
                'store' => url('/api/materials'),
                'update' => url('/api/materials/__ID__'),
                'destroy' => url('/api/materials/__ID__'),
                'show' => url('/api/materials/__ID__'),
            ],
            'filterSchema' => [
                ['key' => 'search', 'label' => 'Search', 'type' => 'text', 'placeholder' => 'Search materials...'],
            ],
        ]);
    }
}
