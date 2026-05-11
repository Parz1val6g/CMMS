<?php
namespace App\Features\Tasks\Services;
use App\Core\Enums\TaskStatus;
use App\Core\Helpers\InputSanitizer;
use App\Core\Services\TransactionHandler;
use App\Features\Tasks\Events\TaskCompletedEvent;
use App\Features\Tasks\Models\Task;
use InvalidArgumentException;
class TaskService
{
    public function __construct(
        private TransactionHandler $transactions
    ) {}

    public function create(array $data, string $managerId): Task
    {
        return $this->transactions->execute(function () use ($data, $managerId) {
            $task = Task::create([
                'service_order_id' => $data['service_order_id'],
                'manager_id' => $managerId,
                'name' => InputSanitizer::sanitize($data['name']),
                'status' => TaskStatus::PENDING->value,
            ]);

            if (isset($data['sector_id'])) {
                $task->sectors()->attach($data['sector_id']);
            }

            return $task;
        });
    }

    public function update(Task $task, array $data): Task
    {
        if (in_array($task->status, [TaskStatus::COMPLETED->value, TaskStatus::CANCELLED->value])) {
            throw new InvalidArgumentException('Cannot update a completed or cancelled task.');
        }
        return $this->transactions->execute(function () use ($task, $data) {
            if (isset($data['name'])) {
                $task->update(['name' => InputSanitizer::sanitize($data['name'])]);
            }
            if (isset($data['sector_id'])) {
                $task->sectors()->sync([$data['sector_id']]);
            }
            return $task;
        });
    }
    public function cancel(Task $task): Task
    {
        if ($task->status === TaskStatus::COMPLETED->value) {
            throw new InvalidArgumentException('Cannot cancel an already completed task.');
        }
        return $this->transactions->execute(function () use ($task) {
            $task->update(['status' => TaskStatus::CANCELLED->value]);
            return $task;
        });
    }
    public function complete(Task $task): Task
    {
        if ($task->status === TaskStatus::COMPLETED->value) {
            return $task; 
        }
        
        return $this->transactions->execute(function () use ($task) {
            $task->update(['status' => TaskStatus::COMPLETED->value]);
            TaskCompletedEvent::dispatch($task);
            return $task;
        });
    }
}