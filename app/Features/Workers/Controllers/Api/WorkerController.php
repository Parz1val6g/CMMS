<?php

namespace App\Features\Workers\Controllers\Api;

use App\Features\Workers\Models\Worker;
use App\Features\Workers\Requests\StoreWorkerRequest;
use App\Features\Workers\Requests\UpdateWorkerRequest;
use App\Features\Workers\Resources\WorkerResource;
use App\Features\Workers\Services\WorkerService;
use App\Core\Services\FilterService;
use App\Core\Traits\FiltersAdvancedRules;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class WorkerController extends Controller
{
    use FiltersAdvancedRules;

    public function __construct(
        private WorkerService $workerService,
        private FilterService $filterService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Worker::with(['user', 'team']);

        if ($request->has('team_id')) {
            $query->where('team_id', $request->team_id);
        }

        if ($request->has('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('user', function ($userQuery) use ($searchTerm) {
                    $userQuery->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$searchTerm])
                              ->orWhere('email', 'like', $searchTerm)
                              ->orWhere('phone', 'like', $searchTerm);
                })->orWhereHas('team', function ($teamQuery) use ($searchTerm) {
                    $teamQuery->where('name', 'like', $searchTerm);
                });
            });
        }

        $this->applyUserRelationshipFilters($request, $query, ['name', 'email', 'phone']);

        $this->applyAdvancedFilters(
            $request, $query, $this->filterService,
            ['created_at']
        );

        $workers = $query
            ->when($request->filled('sort'), fn($q) => $this->filterService->sort($q, $request->sort))
            ->when(!$request->filled('sort'), fn($q) => $q->latest())
            ->paginate(15);

        return WorkerResource::collection($workers);
    }

    public function store(StoreWorkerRequest $request): WorkerResource
    {
        $worker = $this->workerService->create($request->validated());
        $worker->load(['user', 'team']);

        return new WorkerResource($worker);
    }

    public function show(Worker $worker): WorkerResource
    {
        Gate::authorize('view', $worker);

        $worker->load(['user', 'team']);

        return new WorkerResource($worker);
    }

    public function update(UpdateWorkerRequest $request, Worker $worker): WorkerResource
    {
        Gate::authorize('update', $worker);

        $updated = $this->workerService->update($worker, $request->validated());
        $updated->load(['user', 'team']);

        return new WorkerResource($updated);
    }

    public function destroy(Worker $worker): JsonResponse
    {
        Gate::authorize('delete', $worker);

        $this->workerService->delete($worker);

        return response()->json(['message' => 'Worker deleted successfully.']);
    }
}
