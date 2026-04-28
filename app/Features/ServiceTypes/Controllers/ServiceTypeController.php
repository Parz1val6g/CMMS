<?php

namespace App\Features\ServiceTypes\Controllers;

use App\Features\ServiceTypes\Models\ServiceType;
use App\Features\ServiceTypes\Requests\StoreServiceTypeRequest;
use App\Features\ServiceTypes\Requests\UpdateServiceTypeRequest;
use App\Features\ServiceTypes\Resources\ServiceTypeResource;
use App\Features\ServiceTypes\Services\ServiceTypeService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ServiceTypeController extends Controller
{
    public function __construct(
        private ServiceTypeService $serviceTypeService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', ServiceType::class);

        $query = ServiceType::query();

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        $serviceTypes = $query->latest()->paginate(50);

        return ServiceTypeResource::collection($serviceTypes);
    }

    public function store(StoreServiceTypeRequest $request): ServiceTypeResource
    {
        $this->authorize('create', ServiceType::class);

        $serviceType = $this->serviceTypeService->create($request->validated());

        return new ServiceTypeResource($serviceType);
    }

    public function show(ServiceType $serviceType): ServiceTypeResource
    {
        $this->authorize('view', $serviceType);

        return new ServiceTypeResource($serviceType);
    }

    public function update(UpdateServiceTypeRequest $request, ServiceType $serviceType): ServiceTypeResource
    {
        $this->authorize('update', $serviceType);

        $updated = $this->serviceTypeService->update($serviceType, $request->validated());

        return new ServiceTypeResource($updated);
    }

    public function destroy(ServiceType $serviceType): JsonResponse
    {
        $this->authorize('delete', $serviceType);

        $this->serviceTypeService->delete($serviceType);

        return response()->json(['message' => 'Service Type deleted successfully.']);
    }
}
