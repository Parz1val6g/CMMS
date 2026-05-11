<?php

namespace App\Features\Equipments\Controllers\Web;

use App\Core\Enums\EquipmentStatus;
use App\Core\Services\FilterService;
use App\Core\Traits\FiltersAdvancedRules;
use App\Features\Equipments\Models\Equipment;
use App\Features\Equipments\EquipmentFormSchema;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class EquipmentPageController extends Controller
{
    use FiltersAdvancedRules;

    public function __construct(
        private FilterService $filterService
    ) {}

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Equipment::class);

        $query = $this->filterService->apply(
            Equipment::with(['manager']),
            $request->only(['search', 'status', 'from_date', 'to_date', 'sort']),
            ['name', 'serial_number', 'brand', 'model', 'status', 'description']
        );

        // Search across manager name relationship
        if ($request->filled('search')) {
            $term = $request->search;
            $query->orWhereHas('manager', fn($q) => $q
                ->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$term}%"])
            );
        }

        $this->applyAdvancedFilters(
            $request, $query, $this->filterService,
            ['name', 'serial_number', 'brand', 'model', 'status', 'description', 'created_at']
        );

        $items = $query->when(!$request->filled('sort'), fn($q) => $q->latest())->paginate(15)
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
                ['key' => 'name', 'label' => 'Nome', 'sortable' => true],
                ['key' => 'brand', 'label' => 'Marca'],
                ['key' => 'model', 'label' => 'Modelo'],
                ['key' => 'serial_number', 'label' => 'Nº Série'],
                ['key' => 'status', 'label' => 'Estado'],
                ['key' => 'is_loanable', 'label' => 'Emprestável'],
                ['key' => 'next_revision_date', 'label' => 'Próx. Revisão', 'sortable' => true],
                ['key' => 'manager', 'label' => 'Gestor'],
                ['key' => 'created_at', 'label' => 'Criado', 'sortable' => true],
            ],
            'formSchema' => $updateSchema->toArray(),
            'createFormSchema' => $createSchema->toArray(),
            'routes' => [
                'index' => url('/api/equipments'),
                'store' => url('/api/equipments'),
                'update' => url('/api/equipments/__ID__'),
                'destroy' => url('/api/equipments/__ID__'),
                'show' => url('/api/equipments/__ID__'),
            ],
            'advancedFilterFields' => [
                ['value' => 'name',          'label' => 'Nome'],
                ['value' => 'brand',         'label' => 'Marca'],
                ['value' => 'model',         'label' => 'Modelo'],
                ['value' => 'serial_number', 'label' => 'Nº Série'],
                ['value' => 'status',        'label' => 'Estado', 'type' => 'select', 'options' => EquipmentStatus::options()],
                ['value' => 'description',   'label' => 'Descrição'],
                ['value' => 'created_at',    'label' => 'Criado'],
            ],
            'filterSchema' => [
                ['key' => 'search', 'label' => 'Pesquisa', 'type' => 'text', 'placeholder' => 'Pesquisar...'],
                ['key' => 'status', 'label' => 'Estado', 'type' => 'select', 'options' => EquipmentStatus::options()],
            ],
        ]);
    }
}
