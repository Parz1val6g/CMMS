<?php
namespace App\Features\MiniTasks\Services;
use App\Core\Enums\MiniTaskStatus;
use App\Core\Services\TransactionHandler;
use App\Features\MiniTasks\Events\MiniTaskCompletedEvent;
use App\Features\MiniTasks\Models\MiniTask;
use InvalidArgumentException;
class MiniTaskService
{
    public function __construct(
        private TransactionHandler $transactions
    ) {
    }

    /**
     * Create a new MiniTask and assign the worker/team and materials.
     */
    public function create(array $data, string $supervisorId): MiniTask
    {
        return $this->transactions->execute(function () use ($data, $supervisorId) {

            // 1. Create the base record
            $miniTask = MiniTask::create([
                'task_id' => $data['task_id'],
                'supervisor_id' => $supervisorId,
                'description' => $data['description'],
                'status' => MiniTaskStatus::PENDING->value,
            ]);
            // 2. Attach Worker OR Team (Our FormRequest guaranteed only one is present)
            if (!empty($data['worker_id'])) {
                $miniTask->workers()->attach($data['worker_id']);
            } elseif (!empty($data['team_id'])) {
                $miniTask->teams()->attach($data['team_id']);
            }
            // 3. Attach Planned Materials
            if (!empty($data['materials'])) {
                $materialsSync = [];
                foreach ($data['materials'] as $material) {
                    $materialsSync[$material['material_id']] = ['planned_quantity' => $material['planned_quantity']];
                }
                $miniTask->materials()->sync($materialsSync);
            }
            return $miniTask;
        });
    }
    /**
     * Called when the Supervisor manually completes the Mini-Task.
     */
    public function complete(MiniTask $miniTask): MiniTask
    {
        if ($miniTask->status === MiniTaskStatus::COMPLETED->value) {
            throw new InvalidArgumentException('This mini-task is already completed.');
        }
        // Business Logic Validation: Ensure no WorkLogs are still "clocked in" (pending completion)
        $hasPendingWorkLogs = $miniTask->workLogs()->whereNull('completed_at')->exists();
        if ($hasPendingWorkLogs) {
            throw new InvalidArgumentException('Cannot complete mini-task: There are still unfinished work logs.');
        }
        return $this->transactions->execute(function () use ($miniTask) {
            $miniTask->update(['status' => MiniTaskStatus::COMPLETED->value]);
            // Fire the event to trigger the auto-completion check on the parent Task
            MiniTaskCompletedEvent::dispatch($miniTask);
            return $miniTask;
        });
    }
}