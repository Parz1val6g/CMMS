<?php
namespace App\Features\WorkLogs\Services;
use App\Core\Enums\EquipmentStatus;
use App\Core\Enums\WorkLogStatus;
use App\Core\Helpers\InputSanitizer;
use App\Core\Services\TransactionHandler;
use App\Features\Equipments\Models\Equipment;
use App\Features\MiniTasks\Services\MiniTaskService;
use App\Features\WorkLogs\Events\WorkLogCompletedEvent;
use App\Features\WorkLogs\Models\WorkLog;
use InvalidArgumentException;

class WorkLogService
{
    public function __construct(
        private TransactionHandler $transactions,
        private MiniTaskService $miniTaskService,
    ) {}

    /**
     * Create a new work log (Clock-In).
     * Equipment listed moves from RESERVED/ACTIVE → IN_USE.
     */
    public function create(array $data): WorkLog
    {
        return $this->transactions->execute(function () use ($data) {
            $status = $data['completed_at'] ?? null
                ? WorkLogStatus::SUBMITTED->value
                : WorkLogStatus::IN_PROGRESS->value;

            $workLog = WorkLog::create([
                'mini_task_id' => $data['mini_task_id'],
                'started_at'   => $data['started_at'],
                'completed_at' => $data['completed_at'] ?? null,
                'description'  => InputSanitizer::sanitize($data['description']),
                'status'       => $status,
            ]);

            if (!empty($data['worker_ids'])) {
                $workLog->workers()->sync($data['worker_ids']);
            }
            if (!empty($data['materials'])) {
                $workLog->materials()->sync($data['materials']);
            }
            if (!empty($data['equipment_ids'])) {
                $workLog->equipment()->sync($data['equipment_ids']);
                $this->markEquipmentInUse($data['equipment_ids']);
            }

            if ($status === WorkLogStatus::SUBMITTED->value) {
                WorkLogCompletedEvent::dispatch($workLog);
            }

            return $workLog;
        });
    }

    /**
     * Clock-Out: mark completed_at and set status to submitted.
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
                'status'       => WorkLogStatus::SUBMITTED->value,
            ]);
            if (!empty($materials)) {
                $workLog->materials()->syncWithoutDetaching($materials);
            }
            WorkLogCompletedEvent::dispatch($workLog);
            return $workLog;
        });
    }

    /**
     * Manager approves the work log.
     * Equipment is released: back to RESERVED if still planned, otherwise ACTIVE.
     */
    public function approve(WorkLog $workLog, string $reviewerId): WorkLog
    {
        if ($workLog->status !== WorkLogStatus::SUBMITTED->value) {
            throw new InvalidArgumentException('Only submitted work logs can be approved.');
        }
        return $this->transactions->execute(function () use ($workLog, $reviewerId) {
            $workLog->update([
                'status'      => WorkLogStatus::APPROVED->value,
                'reviewed_by' => $reviewerId,
                'reviewed_at' => now(),
            ]);

            // ── Snapshot cost_per_hour into pivot tables ──
            $workLog->loadMissing('workers', 'equipment');

            foreach ($workLog->workers as $worker) {
                $workLog->workers()->updateExistingPivot($worker->id, [
                    'cost_per_hour' => $worker->cost_per_hour,
                ]);
            }

            foreach ($workLog->equipment as $equipment) {
                $workLog->equipment()->updateExistingPivot($equipment->id, [
                    'cost_per_hour' => $equipment->cost_per_hour,
                ]);
            }

            $this->releaseEquipment($workLog);
            return $workLog;
        });
    }

    /**
     * Manager rejects the work log (back to in-progress for rework).
     * Equipment stays IN_USE since the work log is not done yet.
     */
    public function reject(WorkLog $workLog, string $reviewerId): WorkLog
    {
        if ($workLog->status !== WorkLogStatus::SUBMITTED->value) {
            throw new InvalidArgumentException('Only submitted work logs can be rejected.');
        }
        return $this->transactions->execute(function () use ($workLog, $reviewerId) {
            $workLog->update([
                'status'      => WorkLogStatus::REJECTED->value,
                'reviewed_by' => $reviewerId,
                'reviewed_at' => now(),
            ]);
            return $workLog;
        });
    }

    // ── Private helpers ───────────────────────────────────────────────────

    private function markEquipmentInUse(array $equipmentIds): void
    {
        Equipment::whereIn('id', $equipmentIds)
            ->whereIn('status', [EquipmentStatus::ACTIVE->value, EquipmentStatus::RESERVED->value])
            ->each(fn (Equipment $e) => $e->markAsInUse());
    }

    /**
     * After a work log is approved (done), recalculate each equipment's status
     * using the shared logic in MiniTaskService.
     */
    private function releaseEquipment(WorkLog $workLog): void
    {
        $workLog->loadMissing('equipment');

        foreach ($workLog->equipment as $equipment) {
            $this->miniTaskService->recalculateStatus($equipment);
        }
    }
}
