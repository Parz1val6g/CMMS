<?php

namespace App\Features\Equipments\Controllers\Web;

use App\Features\Equipments\Models\EquipmentType;
use App\Features\Equipments\EquipmentTypeFormSchema;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class EquipmentTypePageController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', EquipmentType::class);

        $equipmentTypes = EquipmentType::latest()
            ->paginate(15)
            ->through(fn ($et) => [
                'id' => $et->id,
                'name' => $et->name,
                'category' => $et->category,
                'description' => $et->description,
                'active' => $et->active,
                'created_at' => $et->created_at->format('Y-m-d'),
            ]);

        $createSchema = EquipmentTypeFormSchema::create();
        $updateSchema = EquipmentTypeFormSchema::update();

        return Inertia::render('Equipments/Pages/EquipmentTypesIndex', [
            'equipment_types' => $equipmentTypes,
            'columns' => [
                ['key' => 'name', 'label' => 'Nome', 'sortable' => true],
                ['key' => 'category', 'label' => 'Categoria'],
                ['key' => 'description', 'label' => 'Descrição'],
                ['key' => 'active', 'label' => 'Ativo'],
                ['key' => 'created_at', 'label' => 'Criado', 'sortable' => true],
            ],
            'formSchema' => $updateSchema->toArray(),
            'createFormSchema' => $createSchema->toArray(),
            'routes' => [
                'index' => url('/api/equipment-types'),
                'store' => url('/api/equipment-types'),
                'update' => url('/api/equipment-types/__ID__'),
                'destroy' => url('/api/equipment-types/__ID__'),
                'show' => url('/api/equipment-types/__ID__'),
            ],
            'advancedFilterFields' => [
                ['value' => 'name',        'label' => 'Nome'],
                ['value' => 'category',    'label' => 'Categoria'],
                ['value' => 'description', 'label' => 'Descrição'],
                ['value' => 'created_at',  'label' => 'Criado'],
            ],
            'filterSchema' => [
                ['key' => 'search', 'label' => 'Pesquisa', 'type' => 'text', 'placeholder' => 'Pesquisar...'],
            ],
        ]);
    }
}
