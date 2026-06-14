<?php

namespace App\Features\ServiceOrderCategories\Controllers\Api;

use App\Core\Services\FilterService;
use App\Features\ServiceOrderCategories\Models\ServiceOrderCategory;
use App\Features\ServiceOrderCategories\Requests\StoreServiceOrderCategoryRequest;
use App\Features\ServiceOrderCategories\Requests\UpdateServiceOrderCategoryRequest;
use App\Features\ServiceOrderCategories\Resources\ServiceOrderCategoryResource;
use App\Features\ServiceOrderCategories\Services\ServiceOrderCategoryService;
use App\Http\Controllers\Controller;
use App\Core\Traits\FiltersAdvancedRules;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class ServiceOrderCategoryController extends Controller
{
    use FiltersAdvancedRules;

    public function __construct(
        private ServiceOrderCategoryService $serviceOrderCategoryService,
        private FilterService $filterService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = $this->filterService->apply(
            ServiceOrderCategory::query(),
            $request->only(['search', 'sort']),
            ['name', 'description']
        );

        $this->applyAdvancedFilters(
            $request, $query, $this->filterService,
            ['name', 'description', 'created_at']
        );

        $categories = $query->when(!$request->filled('sort'), fn($q) => $q->latest())->paginate(15);

        return ServiceOrderCategoryResource::collection($categories);
    }

    public function store(StoreServiceOrderCategoryRequest $request): ServiceOrderCategoryResource
    {
        $category = $this->serviceOrderCategoryService->create($request->validated());

        return new ServiceOrderCategoryResource($category);
    }

    public function show(ServiceOrderCategory $serviceOrderCategory): ServiceOrderCategoryResource
    {
        Gate::authorize('view', $serviceOrderCategory);

        return new ServiceOrderCategoryResource($serviceOrderCategory);
    }

    public function update(UpdateServiceOrderCategoryRequest $request, ServiceOrderCategory $serviceOrderCategory): ServiceOrderCategoryResource
    {
        Gate::authorize('update', $serviceOrderCategory);

        $updated = $this->serviceOrderCategoryService->update($serviceOrderCategory, $request->validated());

        return new ServiceOrderCategoryResource($updated);
    }

    public function destroy(ServiceOrderCategory $serviceOrderCategory): JsonResponse
    {
        Gate::authorize('delete', $serviceOrderCategory);

        $this->serviceOrderCategoryService->delete($serviceOrderCategory);

        return response()->json(['message' => 'Service Order Category deleted successfully.']);
    }
}
