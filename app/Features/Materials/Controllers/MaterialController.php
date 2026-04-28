<?php

namespace App\Features\Materials\Controllers;

use App\Features\Materials\Models\Material;
use App\Features\Materials\Requests\StoreMaterialRequest;
use App\Features\Materials\Requests\UpdateMaterialRequest;
use App\Features\Materials\Resources\MaterialResource;
use App\Features\Materials\Services\MaterialService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MaterialController extends Controller
{
    public function __construct(
        private MaterialService $materialService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Material::class);

        $query = Material::with(['unit']);

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $materials = $query->latest()->paginate(50);

        return MaterialResource::collection($materials);
    }

    public function store(StoreMaterialRequest $request): MaterialResource
    {
        $this->authorize('create', Material::class);

        $material = $this->materialService->create($request->validated());
        $material->load(['unit']);

        return new MaterialResource($material);
    }

    public function show(Material $material): MaterialResource
    {
        $this->authorize('view', $material);

        $material->load(['unit']);

        return new MaterialResource($material);
    }

    public function update(UpdateMaterialRequest $request, Material $material): MaterialResource
    {
        $this->authorize('update', $material);

        $updated = $this->materialService->update($material, $request->validated());
        $updated->load(['unit']);

        return new MaterialResource($updated);
    }

    public function destroy(Material $material): \Illuminate\Http\JsonResponse
    {
        $this->authorize('delete', $material);

        $this->materialService->delete($material);

        return response()->json(['message' => 'Material deleted successfully.']);
    }
}
