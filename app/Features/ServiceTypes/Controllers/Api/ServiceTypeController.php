<?php

namespace App\Features\ServiceTypes\Controllers\Api;

use App\Core\Services\FilterService;
use App\Features\ServiceTypes\Models\ServiceType;
use App\Features\ServiceTypes\Requests\StoreServiceTypeRequest;
use App\Features\ServiceTypes\Requests\UpdateServiceTypeRequest;
use App\Features\ServiceTypes\Resources\ServiceTypeResource;
use App\Features\ServiceTypes\Services\ServiceTypeService;
use App\Http\Controllers\Controller;
use App\Core\Traits\FiltersAdvancedRules;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class ServiceTypeController extends Controller
{
    use FiltersAdvancedRules;

    public function __construct(
        private ServiceTypeService $serviceTypeService,
        private FilterService $filterService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', ServiceType::class);

        $query = $this->filterService->apply(
            ServiceType::query(),
            $request->only(['search', 'sort']),
            ['name', 'description']
        );

        $this->applyAdvancedFilters(
            $request, $query, $this->filterService,
            ['name', 'description', 'created_at']
        );

        $serviceTypes = $query->when(!$request->filled('sort'), fn($q) => $q->latest())->paginate(15);

        return ServiceTypeResource::collection($serviceTypes);
    }

    public function store(StoreServiceTypeRequest $request): ServiceTypeResource
    {
        Gate::authorize('create', ServiceType::class);

        $serviceType = $this->serviceTypeService->create($request->validated());

        return new ServiceTypeResource($serviceType);
    }

    public function show(ServiceType $serviceType): ServiceTypeResource
    {
        Gate::authorize('view', $serviceType);

        return new ServiceTypeResource($serviceType);
    }

    public function update(UpdateServiceTypeRequest $request, ServiceType $serviceType): ServiceTypeResource
    {
        Gate::authorize('update', $serviceType);

        $updated = $this->serviceTypeService->update($serviceType, $request->validated());

        return new ServiceTypeResource($updated);
    }

    public function destroy(ServiceType $serviceType): JsonResponse
    {
        Gate::authorize('delete', $serviceType);

        $this->serviceTypeService->delete($serviceType);

        return response()->json(['message' => 'Service Type deleted successfully.']);
    }
}
