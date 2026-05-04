<?php

namespace App\Features\Equipments\Controllers;

use App\Features\Equipments\Models\Equipment;
use App\Features\Equipments\Schemas\EquipmentFormSchema;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class EquipmentPageController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Equipment::class);

        $items = Equipment::with(['manager'])
            ->latest()
            ->paginate(15)
            ->through(fn ($e) => [
                'id'                     => $e->id,
                'name'                   => $e->name,
                'brand'                  => $e->brand,
                'model'                  => $e->model,
                'serial_number'          => $e->serial_number,
                'status'                 => $e->status,
                'is_loanable'            => $e->is_loanable,
                'revision_interval_days' => $e->revision_interval_days,
                'last_revision_date'     => $e->last_revision_date?->format('Y-m-d'),
                'next_revision'          => $e->next_revision_date?->format('Y-m-d'),
                'description'            => $e->description,
                'manager'                => $e->manager ? [
                    'id'   => $e->manager->id,
                    'name' => $e->manager->first_name . ' ' . $e->manager->last_name,
                ] : null,
                'created_at'             => $e->created_at->format('Y-m-d'),
            ]);

        $createSchema = EquipmentFormSchema::create();
        $updateSchema = EquipmentFormSchema::update();

        return Inertia::render('Equipments/Pages/Index', [
            'equipments' => $items,
            'columns' => [
                ['key' => 'name', 'label' => 'Name', 'sortable' => true],
                ['key' => 'brand', 'label' => 'Brand'],
                ['key' => 'model', 'label' => 'Model'],
                ['key' => 'serial_number', 'label' => 'Serial #'],
                ['key' => 'status', 'label' => 'Status'],
                ['key' => 'is_loanable', 'label' => 'Loanable'],
                ['key' => 'next_revision', 'label' => 'Next Revision', 'sortable' => true],
                ['key' => 'manager', 'label' => 'Manager'],
                ['key' => 'created_at', 'label' => 'Created', 'sortable' => true],
            ],
            'formSchema' => $updateSchema->toArray(),
            'createFormSchema' => $createSchema->toArray(),
            'routes' => [
                'index' => url('/equipments'),
                'store' => url('/equipments'),
                'update' => url('/equipments/__ID__'),
                'destroy' => url('/equipments/__ID__'),
                'show' => url('/equipments/__ID__'),
            ],
            'filterSchema' => [
                ['key' => 'search', 'label' => 'Search', 'type' => 'text', 'placeholder' => 'Search equipment...'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => [
                    ['value' => 'active', 'label' => 'Active'],
                    ['value' => 'in_use', 'label' => 'In Use'],
                    ['value' => 'maintenance', 'label' => 'Maintenance'],
                    ['value' => 'retired', 'label' => 'Retired'],
                ]],
            ],
        ]);
    }
}
