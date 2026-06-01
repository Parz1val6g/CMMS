<?php
namespace App\Features\Tasks\Services;
use App\Core\Enums\TaskStatus;
use App\Core\Helpers\InputSanitizer;
use App\Core\Services\TransactionHandler;
use App\Features\Notifications\Services\NotificationService;
use App\Features\Tasks\Events\TaskCompletedEvent;
use App\Features\Tasks\Models\Task;
use App\Features\Tasks\Models\TaskRejection;
use App\Shared\Models\User;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
class TaskService
{
    public function __construct(
        private TransactionHandler $transactions,
        private NotificationService $notifications
    ) {}

    public function create(array $data, string $managerId): Task
    {
        return $this->transactions->execute(function () use ($data, $managerId) {
            $task = Task::create([
                'service_order_id' => $data['service_order_id'],
                'manager_id' => $managerId,
                'description' => InputSanitizer::sanitize($data['description']),
                'status' => TaskStatus::PENDING->value,
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
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
            $updateData = [];
            if (isset($data['description'])) {
                $updateData['description'] = InputSanitizer::sanitize($data['description']);
            }
            if (isset($data['start_date'])) {
                $updateData['start_date'] = $data['start_date'];
            }
            if (isset($data['end_date'])) {
                $updateData['end_date'] = $data['end_date'];
            }
            if (!empty($updateData)) {
                $task->update($updateData);
            }
            if (isset($data['sector_id'])) {
                $task->sectors()->sync([$data['sector_id']]);
            }
            return $task;
        });
    }
    public function markAwaitingApproval(Task $task): Task
    {
        return $this->transactions->execute(function () use ($task) {
            $task->update(['status' => TaskStatus::AWAITING_APPROVAL->value]);
            Cache::tags(["user:{$task->manager_id}"])->flush();
            return $task;
        });
    }

    public function cancel(Task $task): Task
    {
        if ($task->status === TaskStatus::COMPLETED->value) {
            throw new InvalidArgumentException('Cannot cancel an already completed task.');
        }
        if ($task->status === TaskStatus::PENDING && (!$task->start_date || !$task->end_date)) {
            throw new InvalidArgumentException(__('messages.services.task.cannot_cancel_without_period'));
        }
        return $this->transactions->execute(function () use ($task) {
            $task->update(['status' => TaskStatus::CANCELLED->value]);
            Cache::tags(["user:{$task->manager_id}"])->flush();
            return $task;
        });
    }
    public function complete(Task $task): Task
    {
        if ($task->status === TaskStatus::COMPLETED) {
            return $task;
        }

        if ($task->status !== TaskStatus::AWAITING_APPROVAL) {
            throw new InvalidArgumentException('Task must be in awaiting approval status to complete.');
        }

        return $this->transactions->execute(function () use ($task) {
            $task->update(['status' => TaskStatus::COMPLETED->value]);
            TaskCompletedEvent::dispatch($task);
            Cache::tags(["user:{$task->manager_id}"])->flush();
            return $task;
        });
    }

    public function reject(Task $task, User $rejectedBy, string $reason): Task
    {
        if ($task->status !== TaskStatus::AWAITING_APPROVAL) {
            throw new InvalidArgumentException('Task must be in awaiting approval status to be rejected.');
        }

        return $this->transactions->execute(function () use ($task, $rejectedBy, $reason) {
            TaskRejection::create([
                'task_id' => $task->id,
                'rejected_by_id' => $rejectedBy->id,
                'reason' => $reason,
            ]);

            $task->update(['status' => TaskStatus::IN_PROGRESS->value]);
            Cache::tags(["user:{$task->manager_id}"])->flush();

            $this->notifications->create(
                $task->manager_id,
                __('messages.services.notifications.task_rejected_title', ['reference' => $task->reference]),
                __('messages.services.notifications.task_rejected_body', ['reason' => $reason]),
                'task_rejected'
            );

            return $task;
        });
    }
}