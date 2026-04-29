<?php

namespace App\Features\Materials\Controllers;

use App\Features\Materials\Models\Material;
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

        return Inertia::render('Materials/Pages/Index', [
            'materials' => $materials,
            'columns' => [
                ['key' => 'name', 'label' => 'Name', 'sortable' => true],
                ['key' => 'unit', 'label' => 'Unit'],
                ['key' => 'stock_quantity', 'label' => 'Stock', 'sortable' => true],
                ['key' => 'created_at', 'label' => 'Created', 'sortable' => true],
            ],
            'formSchema' => [
                ['key' => 'name', 'label' => 'Name', 'type' => 'text', 'rules' => 'required|max:100'],
                ['key' => 'unit_id', 'label' => 'Unit', 'type' => 'select', 'options' => [], 'rules' => 'required'],
                ['key' => 'stock_quantity', 'label' => 'Stock Quantity', 'type' => 'number', 'rules' => 'required|numeric|min:0'],
            ],
            'createFormSchema' => [
                ['key' => 'name', 'label' => 'Name', 'type' => 'text', 'rules' => 'required|max:100'],
                ['key' => 'unit_id', 'label' => 'Unit', 'type' => 'select', 'options' => [], 'rules' => 'required'],
                ['key' => 'stock_quantity', 'label' => 'Stock Quantity', 'type' => 'number', 'rules' => 'required|numeric|min:0'],
            ],
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
