<?php

namespace App\Features\Teams\Controllers;

use App\Features\Teams\Models\Team;
use App\Features\Teams\Requests\StoreTeamRequest;
use App\Features\Teams\Requests\UpdateTeamRequest;
use App\Features\Teams\Resources\TeamResource;
use App\Features\Teams\Services\TeamService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TeamController extends Controller
{
    public function __construct(
        private TeamService $teamService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Team::class);

        $query = Team::with(['sector']);

        if ($request->has('sector_id')) {
            $query->where('sector_id', $request->sector_id);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $teams = $query->latest()->paginate(50);

        return TeamResource::collection($teams);
    }

    public function store(StoreTeamRequest $request): TeamResource
    {
        $this->authorize('create', Team::class);

        $team = $this->teamService->create($request->validated());
        $team->load(['sector']);

        return new TeamResource($team);
    }

    public function show(Team $team): TeamResource
    {
        $this->authorize('view', $team);

        $team->load(['sector']);

        return new TeamResource($team);
    }

    public function update(UpdateTeamRequest $request, Team $team): TeamResource
    {
        $this->authorize('update', $team);

        $updated = $this->teamService->update($team, $request->validated());
        $updated->load(['sector']);

        return new TeamResource($updated);
    }

    public function destroy(Team $team): JsonResponse
    {
        $this->authorize('delete', $team);

        $this->teamService->delete($team);

        return response()->json(['message' => 'Team deleted successfully.']);
    }
}
