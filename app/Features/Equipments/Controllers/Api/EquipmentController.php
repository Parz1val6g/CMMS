<?php

namespace App\Features\Equipments\Controllers\Api;

use App\Core\Services\FilterService;
use App\Features\Equipments\Models\Equipment;
use App\Features\Equipments\Requests\StoreEquipmentRequest;
use App\Features\Equipments\Requests\UpdateEquipmentRequest;
use App\Features\Equipments\Resources\EquipmentResource;
use App\Features\Equipments\Services\EquipmentService;
use App\Core\Traits\FiltersAdvancedRules;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class EquipmentController extends Controller
{
    use FiltersAdvancedRules;
    public function __construct(
        private EquipmentService $equipmentService,
        private FilterService $filterService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Equipment::class);

        $query = $this->filterService->apply(
            Equipment::with(['manager', 'equipmentType', 'countingType']),
            $request->only(['search', 'status', 'from_date', 'to_date', 'sort']),
            ['name', 'serial_number', 'brand', 'model', 'status', 'description']
        );

        // Search across relationship columns
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

        $items = $query->when(!$request->filled('sort'), fn($q) => $q->latest())->paginate(15);

        return EquipmentResource::collection($items);
    }

    public function store(StoreEquipmentRequest $request): EquipmentResource
    {
        Gate::authorize('create', Equipment::class);

        $equipment = $this->equipmentService->create(
            $request->validated(),
            $request->user()->id
        );
        $equipment->load(['manager', 'equipmentType', 'countingType']);

        return new EquipmentResource($equipment);
    }

    public function show(Equipment $equipment): EquipmentResource
    {
        Gate::authorize('view', $equipment);

        $equipment->load(['manager', 'equipmentType', 'countingType']);

        return new EquipmentResource($equipment);
    }

    public function update(UpdateEquipmentRequest $request, Equipment $equipment): EquipmentResource
    {
        Gate::authorize('update', $equipment);

        $updated = $this->equipmentService->update($equipment, $request->validated());
        $updated->load(['manager', 'equipmentType', 'countingType']);

        return new EquipmentResource($updated);
    }

    public function destroy(Equipment $equipment): JsonResponse
    {
        Gate::authorize('delete', $equipment);

        $this->equipmentService->delete($equipment);

        return response()->json(['message' => 'Equipment deleted successfully.']);
    }
}
