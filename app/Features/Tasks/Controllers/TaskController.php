<?php

namespace App\Features\Tasks\Controllers;

use App\Features\Tasks\Models\Task;
use App\Features\Tasks\Requests\StoreTaskRequest;
use App\Features\Tasks\Requests\UpdateTaskRequest;
use App\Features\Tasks\Resources\TaskResource;
use App\Features\Tasks\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;

class TaskController extends Controller
{
    public function __construct(
        private TaskService $taskService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Task::class);

        $query = Task::with(['sectors', 'manager']);

        if ($request->has('service_order_id')) {
            $query->where('service_order_id', $request->service_order_id);
        }

        if ($request->has('sector_id')) {
            $query->whereHas('sectors', function($q) use ($request) {
                $q->where('sector_id', $request->sector_id);
            });
        }

        $tasks = $query->latest()->paginate(15);
        return TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request): TaskResource
    {
        Gate::authorize('create', Task::class);

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
}
