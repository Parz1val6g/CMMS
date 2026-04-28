<?php

namespace App\Features\Sectors\Controllers;

use App\Features\Sectors\Models\Sector;
use App\Features\Sectors\Requests\StoreSectorRequest;
use App\Features\Sectors\Requests\UpdateSectorRequest;
use App\Features\Sectors\Resources\SectorResource;
use App\Features\Sectors\Services\SectorService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SectorController extends Controller
{
    public function __construct(
        private SectorService $sectorService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Sector::class);

        $query = Sector::with(['head']);

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $sectors = $query->latest()->paginate(50);

        return SectorResource::collection($sectors);
    }

    public function store(StoreSectorRequest $request): SectorResource
    {
        $this->authorize('create', Sector::class);

        $sector = $this->sectorService->create($request->validated());
        $sector->load(['head']);

        return new SectorResource($sector);
    }

    public function show(Sector $sector): SectorResource
    {
        $this->authorize('view', $sector);

        $sector->load(['head']);

        return new SectorResource($sector);
    }

    public function update(UpdateSectorRequest $request, Sector $sector): SectorResource
    {
        $this->authorize('update', $sector);

        $updated = $this->sectorService->update($sector, $request->validated());
        $updated->load(['head']);

        return new SectorResource($updated);
    }

    public function destroy(Sector $sector): JsonResponse
    {
        $this->authorize('delete', $sector);

        $this->sectorService->delete($sector);

        return response()->json(['message' => 'Sector deleted successfully.']);
    }
}