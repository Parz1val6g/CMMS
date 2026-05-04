<?php

namespace App\Features\Equipments\Controllers;

use App\Features\Equipments\Models\Equipment;
use App\Features\Equipments\Requests\StoreEquipmentRequest;
use App\Features\Equipments\Requests\UpdateEquipmentRequest;
use App\Features\Equipments\Resources\EquipmentResource;
use App\Features\Equipments\Services\EquipmentService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class EquipmentController extends Controller
{
    public function __construct(
        private EquipmentService $equipmentService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Equipment::class);

        $query = Equipment::with(['manager']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $items = $query->latest()->paginate(50);

        return EquipmentResource::collection($items);
    }

    public function store(StoreEquipmentRequest $request): EquipmentResource
    {
        Gate::authorize('create', Equipment::class);

        $equipment = $this->equipmentService->create(
            $request->validated(),
            $request->user()->id
        );
        $equipment->load(['manager']);

        return new EquipmentResource($equipment);
    }

    public function show(Equipment $equipment): EquipmentResource
    {
        Gate::authorize('view', $equipment);

        $equipment->load(['manager']);

        return new EquipmentResource($equipment);
    }

    public function update(UpdateEquipmentRequest $request, Equipment $equipment): EquipmentResource
    {
        Gate::authorize('update', $equipment);

        $updated = $this->equipmentService->update($equipment, $request->validated());
        $updated->load(['manager']);

        return new EquipmentResource($updated);
    }

    public function destroy(Equipment $equipment): JsonResponse
    {
        Gate::authorize('delete', $equipment);

        $this->equipmentService->delete($equipment);

        return response()->json(['message' => 'Equipment deleted successfully.']);
    }
}
