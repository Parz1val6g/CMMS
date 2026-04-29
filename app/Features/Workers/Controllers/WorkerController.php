<?php

namespace App\Features\Workers\Controllers;

use App\Features\Workers\Models\Worker;
use App\Features\Workers\Requests\StoreWorkerRequest;
use App\Features\Workers\Requests\UpdateWorkerRequest;
use App\Features\Workers\Resources\WorkerResource;
use App\Features\Workers\Services\WorkerService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class WorkerController extends Controller
{
    public function __construct(
        private WorkerService $workerService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Worker::class);

        $query = Worker::with(['user', 'team']);

        if ($request->has('team_id')) {
            $query->where('team_id', $request->team_id);
        }

        if ($request->has('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->whereHas('user', function ($userQuery) use ($searchTerm) {
                $userQuery->where('first_name', 'like', $searchTerm)
                          ->orWhere('last_name', 'like', $searchTerm);
            });
        }

        $workers = $query->latest()->paginate(50);

        return WorkerResource::collection($workers);
    }

    public function store(StoreWorkerRequest $request): WorkerResource
    {
        Gate::authorize('create', Worker::class);

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
