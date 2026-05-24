<?php

namespace App\Features\Materials\Controllers\Api;

use App\Core\Services\FilterService;
use App\Features\Materials\Models\Material;
use App\Features\Materials\Requests\StoreMaterialRequest;
use App\Features\Materials\Requests\UpdateMaterialRequest;
use App\Features\Materials\Resources\MaterialResource;
use App\Features\Materials\Services\MaterialService;
use App\Core\Traits\FiltersAdvancedRules;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class MaterialController extends Controller
{
    use FiltersAdvancedRules;

    public function __construct(
        private MaterialService $materialService,
        private FilterService $filterService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = $this->filterService->apply(
            Material::with(['unit']),
            $request->only(['search', 'sort']),
            ['name']
        );

        $this->applyAdvancedFilters(
            $request, $query, $this->filterService,
            ['name', 'stock_quantity', 'created_at']
        );

        $materials = $query->when(!$request->filled('sort'), fn($q) => $q->latest())->paginate(15);

        return MaterialResource::collection($materials);
    }

    public function store(StoreMaterialRequest $request): MaterialResource
    {
        $material = $this->materialService->create($request->validated());
        $material->load(['unit']);

        return new MaterialResource($material);
    }

    public function show(Material $material): MaterialResource
    {
        Gate::authorize('view', $material);

        $material->load(['unit']);

        return new MaterialResource($material);
    }

    public function update(UpdateMaterialRequest $request, Material $material): MaterialResource
    {
        Gate::authorize('update', $material);

        $updated = $this->materialService->update($material, $request->validated());
        $updated->load(['unit']);

        return new MaterialResource($updated);
    }

    public function destroy(Material $material): \Illuminate\Http\JsonResponse
    {
        Gate::authorize('delete', $material);

        $this->materialService->delete($material);

        return response()->json(['message' => 'Material deleted successfully.']);
    }
}
