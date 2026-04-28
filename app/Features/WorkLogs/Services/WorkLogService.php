<?php
namespace App\Features\WorkLogs\Services;
use App\Core\Enums\WorkLogStatus;
use App\Core\Services\TransactionHandler;
use App\Features\WorkLogs\Events\WorkLogCompletedEvent;
use App\Features\WorkLogs\Models\WorkLog;
use InvalidArgumentException;
class WorkLogService
{
    public function __construct(
        private TransactionHandler $transactions
    ) {}

    /**
     * Create a new work log (Clock-In)
     */
    public function create(array $data): WorkLog
    {
        return $this->transactions->execute(function () use ($data) {
            $status = $data['completed_at'] ?? null
                ? WorkLogStatus::SUBMITTED->value
                : WorkLogStatus::IN_PROGRESS->value;

            $workLog = WorkLog::create([
                'mini_task_id' => $data['mini_task_id'],
                'started_at' => $data['started_at'],
                'completed_at' => $data['completed_at'] ?? null,
                'description' => $data['description'],
                'status' => $status,
            ]);

            if (!empty($data['worker_ids'])) {
                $workLog->workers()->sync($data['worker_ids']);
            }

            if (!empty($data['materials'])) {
                $workLog->materials()->sync($data['materials']);
            }

            // If completed with clock-out, transition to submitted (pending approval)
            if ($status === WorkLogStatus::SUBMITTED->value) {
                WorkLogCompletedEvent::dispatch($workLog);
            }

            return $workLog;
        });
    }

    /**
     * Clock-Out: mark completed_at and set status to submitted
     */
    public function complete(WorkLog $workLog, string $completedAt, array $materials = []): WorkLog
    {
        if ($workLog->completed_at !== null) {
            throw new InvalidArgumentException('This work log is already completed.');
        }
        if ($workLog->status !== WorkLogStatus::IN_PROGRESS->value) {
            throw new InvalidArgumentException('Only in-progress work logs can be completed.');
        }
        return $this->transactions->execute(function () use ($workLog, $completedAt, $materials) {
            $workLog->update([
                'completed_at' => $completedAt,
                'status' => WorkLogStatus::SUBMITTED->value,
            ]);
            if (!empty($materials)) {
                $workLog->materials()->syncWithoutDetaching($materials);
            }
            // Event fires here to trigger MiniTask cascade
            WorkLogCompletedEvent::dispatch($workLog);
            return $workLog;
        });
    }

    /**
     * Manager approves the work log, triggers the completion cascade
     */
    public function approve(WorkLog $workLog, string $reviewerId): WorkLog
    {
        if ($workLog->status !== WorkLogStatus::SUBMITTED->value) {
            throw new InvalidArgumentException('Only submitted work logs can be approved.');
        }
        return $this->transactions->execute(function () use ($workLog, $reviewerId) {
            $workLog->update([
                'status' => WorkLogStatus::APPROVED->value,
                'reviewed_by' => $reviewerId,
                'reviewed_at' => now(),
            ]);
            return $workLog;
        });
    }

    /**
     * Manager rejects the work log, requires resubmission
     */
    public function reject(WorkLog $workLog, string $reviewerId): WorkLog
    {
        if ($workLog->status !== WorkLogStatus::SUBMITTED->value) {
            throw new InvalidArgumentException('Only submitted work logs can be rejected.');
        }
        return $this->transactions->execute(function () use ($workLog, $reviewerId) {
            $workLog->update([
                'status' => WorkLogStatus::REJECTED->value,
                'reviewed_by' => $reviewerId,
                'reviewed_at' => now(),
            ]);
            return $workLog;
        });
    }
}
