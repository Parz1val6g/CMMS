<?php
namespace App\Features\WorkLogs\Services;
use App\Core\Services\TransactionHandler;
use App\Features\WorkLogs\Events\WorkLogCompletedEvent;
use App\Features\WorkLogs\Models\WorkLog;
use InvalidArgumentException;
class WorkLogService
{
    public function __construct(
        private TransactionHandler $transactions
    ) {
    }
    /**
     * Create a new work log (can be Clock-In OR Immediate Completion)
     */
    public function create(array $data): WorkLog
    {
        return $this->transactions->execute(function () use ($data) {
            $workLog = WorkLog::create([
                'mini_task_id' => $data['mini_task_id'],
                'started_at' => $data['started_at'],
                'completed_at' => $data['completed_at'] ?? null,
                'description' => $data['description'],
            ]);
            // Sync Workers
            if (!empty($data['worker_ids'])) {
                $workLog->workers()->sync($data['worker_ids']);
            }
            // Sync Materials if provided (format: [material_id => ['quantity_used' => 2]])
            if (!empty($data['materials'])) {
                $workLog->materials()->sync($data['materials']);
            }
            // If completed immediately, fire the event!
            if ($workLog->completed_at !== null) {
                WorkLogCompletedEvent::dispatch($workLog);
            }
            return $workLog;
        });
    }
    /**
     * Complete an existing WorkLog (Clock-Out)
     */
    public function complete(WorkLog $workLog, string $completedAt, array $materials = []): WorkLog
    {
        if ($workLog->completed_at !== null) {
            throw new InvalidArgumentException('This work log is already completed.');
        }
        return $this->transactions->execute(function () use ($workLog, $completedAt, $materials) {
            $workLog->update(['completed_at' => $completedAt]);
            if (!empty($materials)) {
                // syncWithoutDetaching ensures we don't delete materials logged previously
                $workLog->materials()->syncWithoutDetaching($materials);
            }
            // Fire the event to trigger the MiniTask check!
            WorkLogCompletedEvent::dispatch($workLog);
            return $workLog;
        });
    }
}