<?php

namespace App\Features\Tasks\Controllers\Api;

use App\Core\Services\FilterService;
use App\Features\Tasks\Models\Task;
use App\Features\Tasks\Requests\RejectTaskRequest;
use App\Features\Tasks\Requests\StoreTaskRequest;
use App\Features\Tasks\Requests\UpdateTaskRequest;
use App\Features\Tasks\Resources\TaskRejectionResource;
use App\Features\Tasks\Resources\TaskResource;
use App\Features\Tasks\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use App\Core\Traits\FiltersAdvancedRules;
use App\Http\Controllers\Controller;

class TaskController extends Controller
{
    use FiltersAdvancedRules;
    public function __construct(
        private TaskService $taskService,
        private FilterService $filterService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = $this->filterService->apply(
            Task::with(['sectors', 'manager', 'serviceOrder']),
            $request->only(['search', 'status', 'priority', 'from_date', 'to_date', 'sort']),
            ['description', 'status']
        );

        // Search across relationship columns
        if ($request->filled('search')) {
            $term = $request->search;
            $query->orWhereHas('serviceOrder', fn($q) => $q
                ->where('process', 'like', "%{$term}%")
            );
            $query->orWhereHas('manager', fn($q) => $q
                ->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$term}%"])
            );
        }

        if ($request->has('service_order_id')) {
            $query->where('service_order_id', $request->service_order_id);
        }

        if ($request->has('sector_id')) {
            $query->whereHas('sectors', function($q) use ($request) {
                $q->where('sector_id', $request->sector_id);
            });
        }

        $this->applyAdvancedFilters(
            $request, $query, $this->filterService,
            ['description', 'status', 'priority', 'created_at']
        );

        $tasks = $query->when(!$request->filled('sort'), fn($q) => $q->latest())->paginate(15);
        return TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request): TaskResource
    {
        $task = $this->taskService->create(
            $request->validated(),
            $request->user()->id
        );

        $task->load(['sectors', 'manager']);
        return new TaskResource($task);
    }

    public function show(Task $task): TaskResource
    {
        Gate::authorize('view', $task);

        $task->load(['sectors', 'manager', 'miniTasks', 'serviceOrder']);
        return new TaskResource($task);
    }

    public function update(UpdateTaskRequest $request, Task $task): TaskResource
    {
        Gate::authorize('update', $task);

        $updatedTask = $this->taskService->update($task, $request->validated());
        $updatedTask->load(['sectors', 'manager']);
        return new TaskResource($updatedTask);
    }

    public function cancel(Task $task): TaskResource
    {
        Gate::authorize('cancel', $task);

        $cancelledTask = $this->taskService->cancel($task);
        $cancelledTask->load(['sectors', 'manager']);
        return new TaskResource($cancelledTask);
    }

    public function destroy(Task $task): JsonResponse
    {
        Gate::authorize('delete', $task);

        $task->delete();
        return response()->json(['message' => 'Task deleted successfully']);
    }

    public function complete(Task $task): TaskResource
    {
        Gate::authorize('complete', $task);

        $completedTask = $this->taskService->complete($task);
        $completedTask->load(['sectors', 'manager']);
        return new TaskResource($completedTask);
    }

    public function reject(RejectTaskRequest $request, Task $task): TaskResource
    {
        Gate::authorize('reject', $task);

        $rejectedTask = $this->taskService->reject(
            $task,
            $request->user(),
            $request->validated('reason')
        );
        $rejectedTask->load(['sectors', 'manager']);
        return new TaskResource($rejectedTask);
    }

    public function rejections(Task $task): AnonymousResourceCollection
    {
        Gate::authorize('view', $task);

        $rejections = $task->rejections()
            ->with('rejectedBy')
            ->orderByDesc('created_at')
            ->get();

        return TaskRejectionResource::collection($rejections);
    }
}
