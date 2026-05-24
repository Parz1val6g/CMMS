<?php

namespace App\Features\Sectors\Controllers\Api;

use App\Core\Services\FilterService;
use App\Features\Sectors\Models\Sector;
use App\Features\Sectors\Requests\StoreSectorRequest;
use App\Features\Sectors\Requests\UpdateSectorRequest;
use App\Features\Sectors\Resources\SectorResource;
use App\Features\Sectors\Services\SectorService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use App\Core\Traits\FiltersAdvancedRules;

class SectorController extends Controller
{
    use FiltersAdvancedRules;

    public function __construct(
        private SectorService $sectorService,
        private FilterService $filterService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = $this->filterService->apply(
            Sector::with(['head']),
            $request->only(['search', 'sort']),
            ['name']
        );

        // Search across relationship columns
        if ($request->filled('search')) {
            $term = $request->search;
            $query->orWhereHas('head', fn($q) => $q
                ->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$term}%"])
            );
        }

        $this->applyAdvancedFilters(
            $request, $query, $this->filterService,
            ['name', 'created_at']
        );

        $sectors = $query->when(!$request->filled('sort'), fn($q) => $q->latest())->paginate(15);

        return SectorResource::collection($sectors);
    }

    public function store(StoreSectorRequest $request): SectorResource
    {
        $sector = $this->sectorService->create($request->validated());
        $sector->load(['head']);

        return new SectorResource($sector);
    }

    public function show(Sector $sector): SectorResource
    {
        Gate::authorize('view', $sector);

        $sector->load(['head']);

        return new SectorResource($sector);
    }

    public function update(UpdateSectorRequest $request, Sector $sector): SectorResource
    {
        Gate::authorize('update', $sector);

        $updated = $this->sectorService->update($sector, $request->validated());
        $updated->load(['head']);

        return new SectorResource($updated);
    }

    public function destroy(Sector $sector): JsonResponse
    {
        Gate::authorize('delete', $sector);

        $this->sectorService->delete($sector);

        return response()->json(['message' => 'Sector deleted successfully.']);
    }
}
