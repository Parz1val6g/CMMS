<?php

namespace App\Features\Equipments\Controllers\Web;

use App\Core\Traits\GatesRoutes;
use App\Features\Equipments\EquipmentRevisionFormSchema;
use App\Features\Equipments\Models\EquipmentRevision;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class EquipmentRevisionPageController extends Controller
{
    use GatesRoutes;

    public function index(Request $request)
    {
        Gate::authorize('viewAny', EquipmentRevision::class);

        $revisions = EquipmentRevision::with('equipment')->latest()
            ->paginate(15)
            ->through(fn($r) => [
                'id'            => $r->id,
                'equipment'     => $r->equipment?->name,
                'status'        => $r->status,
                'revision_date' => $r->revision_date?->format('Y-m-d'),
                'notes'         => $r->notes,
                'created_at'    => $r->created_at->format('Y-m-d'),
            ]);

        $createSchema = EquipmentRevisionFormSchema::create();
        $updateSchema = EquipmentRevisionFormSchema::update();

        return Inertia::render('Equipments/Pages/EquipmentRevisionsIndex', [
            'equipment_revisions' => $revisions,
            'columns' => [
                ['key' => 'equipment',     'label' => 'Equipamento'],
                ['key' => 'status',        'label' => 'Estado',      'sortable' => true],
                ['key' => 'revision_date', 'label' => 'Data Revisao', 'sortable' => true],
                ['key' => 'notes',         'label' => 'Notas'],
                ['key' => 'created_at',    'label' => 'Criado',       'sortable' => true],
            ],
            'formSchema'       => $updateSchema->toArray(),
            'createFormSchema' => $createSchema->toArray(),
            'routes' => $this->gatedRoutes([
                'index'   => url('/api/equipment-revisions'),
                'store'   => url('/api/equipment-revisions'),
                'update'  => url('/api/equipment-revisions/__ID__'),
                'destroy' => url('/api/equipment-revisions/__ID__'),
                'show'    => url('/api/equipment-revisions/__ID__'),
            ], 'equipment_revisions'),
            'advancedFilterFields' => [
                ['value' => 'status',        'label' => 'Estado'],
                ['value' => 'revision_date', 'label' => 'Data Revisao'],
                ['value' => 'created_at',    'label' => 'Criado'],
            ],
            'filterSchema' => [
                ['key' => 'search', 'label' => 'Pesquisa', 'type' => 'text', 'placeholder' => 'Pesquisar...'],
            ],
        ]);
    }
}
