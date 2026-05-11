<?php

namespace App\Features\Locations\Controllers\Api;

use App\Core\Services\FilterService;
use App\Features\Locations\Models\Location;
use App\Features\Locations\Requests\StoreLocationRequest;
use App\Features\Locations\Requests\UpdateLocationRequest;
use App\Features\Locations\Resources\LocationResource;
use App\Features\Locations\Services\LocationService;
use App\Http\Controllers\Controller;
use App\Core\Traits\FiltersAdvancedRules;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class LocationController extends Controller
{
    use FiltersAdvancedRules;

    public function __construct(
        private LocationService $locationService,
        private FilterService $filterService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Location::class);

        $query = $this->filterService->apply(
            Location::with(['parish.municipality']),
            $request->only(['search', 'sort']),
            ['street_address', 'postal_code', 'landmark']
        );

        $this->applyAdvancedFilters(
            $request, $query, $this->filterService,
            ['street_address', 'postal_code', 'landmark', 'created_at']
        );

        $locations = $query->when(!$request->filled('sort'), fn($q) => $q->latest())->paginate(15);

        return LocationResource::collection($locations);
    }

    public function store(StoreLocationRequest $request): LocationResource
    {
        Gate::authorize('create', Location::class);

        $location = $this->locationService->create($request->validated());
        $location->load(['parish.municipality']);

        return new LocationResource($location);
    }

    public function show(Location $location): LocationResource
    {
        Gate::authorize('view', $location);

        $location->load(['parish.municipality']);

        return new LocationResource($location);
    }

    public function update(UpdateLocationRequest $request, Location $location): LocationResource
    {
        Gate::authorize('update', $location);

        $updated = $this->locationService->update($location, $request->validated());
        $updated->load(['parish.municipality']);

        return new LocationResource($updated);
    }

    public function destroy(Location $location): \Illuminate\Http\JsonResponse
    {
        Gate::authorize('delete', $location);

        $this->locationService->delete($location);

        return response()->json(['message' => 'Location deleted successfully.']);
    }
}
