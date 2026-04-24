<?php
namespace App\Features\Tasks\Services;
use App\Core\Enums\TaskStatus;
use App\Core\Services\TransactionHandler;
use App\Features\Tasks\Events\TaskCompletedEvent;
use App\Features\Tasks\Models\Task;
use InvalidArgumentException;
class TaskService
{
    public function __construct(
        private TransactionHandler $transactions
    ) {}
    public function update(Task $task, array $data): Task
    {
        if (in_array($task->status, [TaskStatus::COMPLETED->value, 'cancelled'])) {
            throw new InvalidArgumentException('Cannot update a completed or cancelled task.');
        }
        return $this->transactions->execute(function () use ($task, $data) {
            if (isset($data['name'])) {
                $task->update(['name' => $data['name']]);
            }
            if (isset($data['sector_ids'])) {
                $task->sectors()->sync($data['sector_ids']);
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
            $task->update(['status' => 'cancelled']);
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