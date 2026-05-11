<?php
namespace App\Features\MiniTasks\Services;
use App\Core\Enums\EquipmentStatus;
use App\Core\Enums\MiniTaskStatus;
use App\Core\Enums\WorkLogStatus;
use App\Core\Helpers\InputSanitizer;
use App\Core\Services\TransactionHandler;
use App\Features\Equipments\Models\Equipment;
use App\Features\MiniTasks\Events\MiniTaskCompletedEvent;
use App\Features\MiniTasks\Models\MiniTask;
use InvalidArgumentException;

class MiniTaskService
{
    public function __construct(
        private TransactionHandler $transactions
    ) {}

    /**
     * Create a new MiniTask and assign workers, teams, materials, and planned equipment.
     */
    public function create(array $data, string $supervisorId): MiniTask
    {
        return $this->transactions->execute(function () use ($data, $supervisorId) {
            $miniTask = MiniTask::create([
                'task_id'      => $data['task_id'],
                'supervisor_id'=> $supervisorId,
                'description'  => InputSanitizer::sanitize($data['description']),
                'status'       => MiniTaskStatus::PENDING->value,
            ]);

            $miniTask->workers()->sync($data['worker_ids'] ?? []);
            $miniTask->teams()->sync($data['team_ids'] ?? []);

            if (!empty($data['materials'])) {
                $materialsSync = [];
                foreach ($data['materials'] as $material) {
                    $materialsSync[$material['material_id']] = ['planned_quantity' => $material['planned_quantity']];
                }
                $miniTask->materials()->sync($materialsSync);
            }

            $this->syncEquipment($miniTask, $data['equipment_ids'] ?? []);

            return $miniTask;
        });
    }

    /**
     * Update a mini-task's planned equipment (and re-evaluate statuses).
     */
    public function updateEquipment(MiniTask $miniTask, array $equipmentIds): void
    {
        $this->transactions->execute(function () use ($miniTask, $equipmentIds) {
            $this->releaseEquipment($miniTask);
            $this->syncEquipment($miniTask, $equipmentIds);
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
        $hasPendingWorkLogs = $miniTask->workLogs()->whereNull('completed_at')->exists();
        if ($hasPendingWorkLogs) {
            throw new InvalidArgumentException('Cannot complete mini-task: There are still unfinished work logs.');
        }
        return $this->transactions->execute(function () use ($miniTask) {
            $this->releaseEquipment($miniTask);
            $miniTask->update(['status' => MiniTaskStatus::COMPLETED->value]);
            MiniTaskCompletedEvent::dispatch($miniTask);
            return $miniTask;
        });
    }

    /**
     * Cancel a mini-task and release its planned equipment.
     */
    public function cancel(MiniTask $miniTask): MiniTask
    {
        if ($miniTask->status->isClosed()) {
            throw new InvalidArgumentException('This mini-task is already closed.');
        }
        return $this->transactions->execute(function () use ($miniTask) {
            $this->releaseEquipment($miniTask);
            $miniTask->update(['status' => MiniTaskStatus::CANCELLED->value]);
            return $miniTask;
        });
    }

    // ── Private helpers ───────────────────────────────────────────────────

    /**
     * Sync planned equipment for a mini-task and mark ACTIVE ones as RESERVED.
     * Equipment already IN_USE (e.g. in a work log) stays IN_USE — no downgrade.
     */
    private function syncEquipment(MiniTask $miniTask, array $equipmentIds): void
    {
        $miniTask->equipment()->sync($equipmentIds);

        if (empty($equipmentIds)) {
            return;
        }

        Equipment::whereIn('id', $equipmentIds)
            ->where('status', EquipmentStatus::ACTIVE->value)
            ->each(fn (Equipment $e) => $e->markAsReserved());
    }

    /**
     * Release equipment that was planned in this mini-task back to the correct state.
     * - Still used in an active work log → stays IN_USE
     * - Still planned in another active mini-task → stays RESERVED
     * - Neither → ACTIVE
     */
    private function releaseEquipment(MiniTask $miniTask): void
    {
        $miniTask->loadMissing('equipment');

        foreach ($miniTask->equipment as $equipment) {
            $this->recalculateStatus($equipment, excludeMiniTask: $miniTask->id);
        }
    }

    /**
     * Recalculate the correct status for a piece of equipment after a change,
     * optionally excluding a mini-task that is being removed/closed.
     */
    public function recalculateStatus(Equipment $equipment, ?string $excludeMiniTask = null): void
    {
        // Highest priority: actively used in a work log (IN_PROGRESS or SUBMITTED)
        $inActiveWorkLog = $equipment->workLogs()
            ->whereIn('status', [WorkLogStatus::IN_PROGRESS->value, WorkLogStatus::SUBMITTED->value])
            ->exists();

        if ($inActiveWorkLog) {
            if ($equipment->status !== EquipmentStatus::IN_USE) {
                $equipment->markAsInUse();
            }
            return;
        }

        // Second: planned in an active mini-task
        $query = $equipment->miniTasks()
            ->whereNotIn('status', [MiniTaskStatus::COMPLETED->value, MiniTaskStatus::CANCELLED->value]);

        if ($excludeMiniTask) {
            $query->where('mini_tasks.id', '!=', $excludeMiniTask);
        }

        if ($query->exists()) {
            if ($equipment->status !== EquipmentStatus::RESERVED) {
                $equipment->markAsReserved();
            }
            return;
        }

        // Nothing holding it — return to active (skip if already active)
        if ($equipment->status !== EquipmentStatus::ACTIVE) {
            $equipment->markAsActive();
        }
    }
}