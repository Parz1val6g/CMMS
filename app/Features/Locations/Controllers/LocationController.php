<?php

namespace App\Features\Locations\Controllers;

use App\Features\Locations\Models\Location;
use App\Features\Locations\Requests\StoreLocationRequest;
use App\Features\Locations\Requests\UpdateLocationRequest;
use App\Features\Locations\Resources\LocationResource;
use App\Features\Locations\Services\LocationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LocationController extends Controller
{
    public function __construct(
        private LocationService $locationService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Location::class);

        $query = Location::with(['parish.municipality']);

        if ($request->has('search')) {
            $query->where('street_address', 'like', '%' . $request->search . '%')
                  ->orWhere('postal_code', 'like', '%' . $request->search . '%')
                  ->orWhere('landmark', 'like', '%' . $request->search . '%');
        }

        $locations = $query->latest()->paginate(50);

        return LocationResource::collection($locations);
    }

    public function store(StoreLocationRequest $request): LocationResource
    {
        $this->authorize('create', Location::class);

        $location = $this->locationService->create($request->validated());
        $location->load(['parish.municipality']);

        return new LocationResource($location);
    }

    public function show(Location $location): LocationResource
    {
        $this->authorize('view', $location);

        $location->load(['parish.municipality']);

        return new LocationResource($location);
    }

    public function update(UpdateLocationRequest $request, Location $location): LocationResource
    {
        $this->authorize('update', $location);

        $updated = $this->locationService->update($location, $request->validated());
        $updated->load(['parish.municipality']);

        return new LocationResource($updated);
    }

    public function destroy(Location $location): \Illuminate\Http\JsonResponse
    {
        $this->authorize('delete', $location);

        $this->locationService->delete($location);

        return response()->json(['message' => 'Location deleted successfully.']);
    }
}
