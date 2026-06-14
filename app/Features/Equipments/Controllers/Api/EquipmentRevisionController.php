<?php

namespace App\Features\Equipments\Controllers\Api;

use App\Core\Services\FilterService;
use App\Features\Equipments\Models\EquipmentRevision;
use App\Features\Equipments\Requests\StoreEquipmentRevisionRequest;
use App\Features\Equipments\Requests\UpdateEquipmentRevisionRequest;
use App\Features\Equipments\Resources\EquipmentRevisionResource;
use App\Features\Equipments\Services\EquipmentRevisionService;
use App\Http\Controllers\Controller;
use App\Core\Traits\FiltersAdvancedRules;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class EquipmentRevisionController extends Controller
{
    use FiltersAdvancedRules;

    public function __construct(
        private EquipmentRevisionService $equipmentRevisionService,
        private FilterService $filterService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = $this->filterService->apply(
            EquipmentRevision::with('equipment'),
            $request->only(['search', 'sort']),
            ['notes']
        );

        $this->applyAdvancedFilters(
            $request, $query, $this->filterService,
            ['status', 'revision_date', 'created_at']
        );

        $revisions = $query->when(!$request->filled('sort'), fn($q) => $q->latest())->paginate(15);

        return EquipmentRevisionResource::collection($revisions);
    }

    public function store(StoreEquipmentRevisionRequest $request): EquipmentRevisionResource
    {
        $revision = $this->equipmentRevisionService->create($request->validated());

        return new EquipmentRevisionResource($revision);
    }

    public function show(EquipmentRevision $equipmentRevision): EquipmentRevisionResource
    {
        Gate::authorize('view', $equipmentRevision);

        return new EquipmentRevisionResource($equipmentRevision->load('equipment'));
    }

    public function update(UpdateEquipmentRevisionRequest $request, EquipmentRevision $equipmentRevision): EquipmentRevisionResource
    {
        Gate::authorize('update', $equipmentRevision);

        $updated = $this->equipmentRevisionService->update($equipmentRevision, $request->validated());

        return new EquipmentRevisionResource($updated);
    }

    public function destroy(EquipmentRevision $equipmentRevision): JsonResponse
    {
        Gate::authorize('delete', $equipmentRevision);

        $this->equipmentRevisionService->delete($equipmentRevision);

        return response()->json(['message' => 'Equipment Revision deleted successfully.']);
    }
}
