<?php

namespace App\Features\Equipments\Controllers\Api;

use App\Core\Services\FilterService;
use App\Features\Equipments\Models\CountingType;
use App\Features\Equipments\Requests\StoreCountingTypeRequest;
use App\Features\Equipments\Requests\UpdateCountingTypeRequest;
use App\Features\Equipments\Resources\CountingTypeResource;
use App\Features\Equipments\Services\CountingTypeService;
use App\Http\Controllers\Controller;
use App\Core\Traits\FiltersAdvancedRules;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class CountingTypeController extends Controller
{
    use FiltersAdvancedRules;

    public function __construct(
        private CountingTypeService $countingTypeService,
        private FilterService $filterService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = $this->filterService->apply(
            CountingType::query(),
            $request->only(['search', 'sort']),
            ['name', 'value']
        );

        $this->applyAdvancedFilters(
            $request, $query, $this->filterService,
            ['name', 'value', 'active', 'created_at']
        );

        $countingTypes = $query->when(!$request->filled('sort'), fn($q) => $q->latest())->paginate(15);

        return CountingTypeResource::collection($countingTypes);
    }

    public function store(StoreCountingTypeRequest $request): CountingTypeResource
    {
        $countingType = $this->countingTypeService->create($request->validated());

        return new CountingTypeResource($countingType);
    }

    public function show(CountingType $countingType): CountingTypeResource
    {
        Gate::authorize('view', $countingType);

        return new CountingTypeResource($countingType);
    }

    public function update(UpdateCountingTypeRequest $request, CountingType $countingType): CountingTypeResource
    {
        Gate::authorize('update', $countingType);

        $updated = $this->countingTypeService->update($countingType, $request->validated());

        return new CountingTypeResource($updated);
    }

    public function destroy(CountingType $countingType): JsonResponse
    {
        Gate::authorize('delete', $countingType);

        $this->countingTypeService->delete($countingType);

        return response()->json(['message' => 'Counting Type deleted successfully.']);
    }
}
