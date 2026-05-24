<?php

namespace App\Features\Teams\Controllers\Api;

use App\Core\Services\FilterService;
use App\Features\Teams\Models\Team;
use App\Features\Teams\Requests\StoreTeamRequest;
use App\Features\Teams\Requests\UpdateTeamRequest;
use App\Features\Teams\Resources\TeamResource;
use App\Features\Teams\Services\TeamService;
use App\Core\Traits\FiltersAdvancedRules;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class TeamController extends Controller
{
    use FiltersAdvancedRules;

    public function __construct(
        private TeamService $teamService,
        private FilterService $filterService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = $this->filterService->apply(
            Team::with(['sector', 'responsible']),
            $request->only(['search', 'sort']),
            ['name']
        );

        // Search across relationship columns
        if ($request->filled('search')) {
            $term = $request->search;
            $query->orWhereHas('sector', fn($q) => $q
                ->where('name', 'like', "%{$term}%")
            );
        }

        if ($request->has('sector_id')) {
            $query->where('sector_id', $request->sector_id);
        }

        $this->applyAdvancedFilters(
            $request, $query, $this->filterService,
            ['name', 'created_at']
        );

        $teams = $query->when(!$request->filled('sort'), fn($q) => $q->latest())->paginate(15);

        return TeamResource::collection($teams);
    }

    public function store(StoreTeamRequest $request): TeamResource
    {
        $team = $this->teamService->create($request->validated());
        $team->load(['sector', 'responsible']);

        return new TeamResource($team);
    }

    public function show(Team $team): TeamResource
    {
        Gate::authorize('view', $team);

        $team->load(['sector', 'responsible']);

        return new TeamResource($team);
    }

    public function update(UpdateTeamRequest $request, Team $team): TeamResource
    {
        Gate::authorize('update', $team);

        $updated = $this->teamService->update($team, $request->validated());
        $updated->load(['sector', 'responsible']);

        return new TeamResource($updated);
    }

    public function destroy(Team $team): JsonResponse
    {
        Gate::authorize('delete', $team);

        $this->teamService->delete($team);

        return response()->json(['message' => 'Team deleted successfully.']);
    }
}
