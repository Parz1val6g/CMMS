<?php

namespace App\Features\Equipments\Controllers\Api;

use App\Core\Services\FilterService;
use App\Features\Equipments\Models\EquipmentType;
use App\Features\Equipments\Requests\StoreEquipmentTypeRequest;
use App\Features\Equipments\Requests\UpdateEquipmentTypeRequest;
use App\Features\Equipments\Resources\EquipmentTypeResource;
use App\Features\Equipments\Services\EquipmentTypeService;
use App\Http\Controllers\Controller;
use App\Core\Traits\FiltersAdvancedRules;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class EquipmentTypeController extends Controller
{
    use FiltersAdvancedRules;

    public function __construct(
        private EquipmentTypeService $equipmentTypeService,
        private FilterService $filterService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', EquipmentType::class);

        $query = $this->filterService->apply(
            EquipmentType::query(),
            $request->only(['search', 'sort']),
            ['name', 'category', 'description']
        );

        $this->applyAdvancedFilters(
            $request, $query, $this->filterService,
            ['name', 'category', 'description', 'active', 'created_at']
        );

        $equipmentTypes = $query->when(!$request->filled('sort'), fn($q) => $q->latest())->paginate(15);

        return EquipmentTypeResource::collection($equipmentTypes);
    }

    public function store(StoreEquipmentTypeRequest $request): EquipmentTypeResource
    {
        Gate::authorize('create', EquipmentType::class);

        $equipmentType = $this->equipmentTypeService->create($request->validated());

        return new EquipmentTypeResource($equipmentType);
    }

    public function show(EquipmentType $equipmentType): EquipmentTypeResource
    {
        Gate::authorize('view', $equipmentType);

        return new EquipmentTypeResource($equipmentType);
    }

    public function update(UpdateEquipmentTypeRequest $request, EquipmentType $equipmentType): EquipmentTypeResource
    {
        Gate::authorize('update', $equipmentType);

        $updated = $this->equipmentTypeService->update($equipmentType, $request->validated());

        return new EquipmentTypeResource($updated);
    }

    public function destroy(EquipmentType $equipmentType): JsonResponse
    {
        Gate::authorize('delete', $equipmentType);

        $this->equipmentTypeService->delete($equipmentType);

        return response()->json(['message' => 'Equipment Type deleted successfully.']);
    }
}
