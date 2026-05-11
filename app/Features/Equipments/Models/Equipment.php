<?php

namespace App\Features\Equipments\Models;

use App\Core\Enums\EquipmentStatus;
use App\Core\Traits\Base;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Shared\Models\User;
use App\Features\WorkLogs\Models\WorkLog;
use App\Features\ServiceOrders\Models\ServiceOrder;

class Equipment extends Model
{
    use Base;

    protected $table = 'equipments';

    protected $fillable = [
        'name',
        'brand',
        'model',
        'serial_number',
        'manager_id',
        'status',
        'is_loanable',
        'revision_interval_days',
        'last_revision_date',
        'next_revision_date',
        'description',
    ];

    protected $casts = [
        'last_revision_date' => 'datetime',
        'next_revision_date' => 'datetime',
        'is_loanable' => 'boolean',
        'revision_interval_days' => 'integer',
        'status' => EquipmentStatus::class,
    ];

    /**
     * Equipment manager (user responsible for this equipment)
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Work logs that used this equipment
     */
    public function workLogs(): BelongsToMany
    {
        return $this->belongsToMany(
            WorkLog::class,
            'work_log_equipment',
            'equipment_id',
            'work_log_id'
        )->withTimestamps();
    }

    /**
     * Mini-tasks that have this equipment planned (RESERVED state)
     */
    public function miniTasks(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Features\MiniTasks\Models\MiniTask::class,
            'mini_task_equipment',
            'equipment_id',
            'mini_task_id'
        )->withTimestamps();
    }

    /**
     * Service orders that involve this equipment (LOAN, etc.)
     */
    public function serviceOrders(): BelongsToMany
    {
        return $this->belongsToMany(ServiceOrder::class, 'equipment_service_order')
            ->withTimestamps();
    }

    /**
     * Equipment revisions (approval history)
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(EquipmentRevision::class, 'equipment_id');
    }

    // ─── Scopes ───────────────────────────────────────────────────

    /**
     * Scope: Get only loanable equipment
     */
    public function scopeLoanable(Builder $query): Builder
    {
        return $query->where('is_loanable', true);
    }

    /**
     * Scope: Get only for a specific manager
     */
    public function scopeForManager(Builder $query, User $user): Builder
    {
        return $query->where('manager_id', $user->id);
    }

    /**
     * Scope: Equipment that is overdue for revision
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereNotNull('next_revision_date')
            ->where('next_revision_date', '<', now());
    }

    // ─── Status Checks ────────────────────────────────────────────

    /**
     * Check if equipment is overdue for revision
     */
    public function isOverdue(): bool
    {
        return $this->next_revision_date && $this->next_revision_date < now();
    }

    /**
     * Check if equipment needs revision today
     */
    public function needsRevisionToday(): bool
    {
        return $this->next_revision_date?->isToday() ?? false;
    }

    /**
     * Check if equipment can be loaned to clients (policy level)
     */
    public function canBeLoanedToClient(): bool
    {
        return $this->is_loanable === true;
    }

    /**
     * Check if equipment is available for a new loan.
     * Conditions: loanable + active status + revision not overdue.
     */
    public function isAvailableForLoan(): bool
    {
        if ($this->is_loanable !== true) {
            return false;
        }
        if (!$this->status instanceof EquipmentStatus || !$this->status->isAvailableForLoan()) {
            return false;
        }
        if ($this->isOverdue()) {
            return false;
        }
        return true;
    }

    /**
     * Check if this equipment can transition to a target state.
     */
    public function canTransitionTo(EquipmentStatus $target): bool
    {
        $current = $this->status;

        // active → reserved (planned in mini-task)
        if ($current === EquipmentStatus::ACTIVE && $target === EquipmentStatus::RESERVED) {
            return true;
        }
        // reserved → active (removed from all mini-tasks)
        if ($current === EquipmentStatus::RESERVED && $target === EquipmentStatus::ACTIVE) {
            return true;
        }
        // reserved → in_use (work log or loan started)
        if ($current === EquipmentStatus::RESERVED && $target === EquipmentStatus::IN_USE) {
            return true;
        }
        // active → in_use (loan checkout or work log with no prior reservation)
        if ($current === EquipmentStatus::ACTIVE && $target === EquipmentStatus::IN_USE) {
            return true;
        }
        // in_use → reserved (work log done, still planned in a mini-task)
        if ($current === EquipmentStatus::IN_USE && $target === EquipmentStatus::RESERVED) {
            return true;
        }
        // in_use → active (loan return / work log done, no pending plans)
        if ($current === EquipmentStatus::IN_USE && $target === EquipmentStatus::ACTIVE) {
            return true;
        }
        // any → maintenance_pending
        if ($target === EquipmentStatus::MAINTENANCE_PENDING) {
            return true;
        }
        // maintenance_pending → under_maintenance
        if ($current === EquipmentStatus::MAINTENANCE_PENDING && $target === EquipmentStatus::UNDER_MAINTENANCE) {
            return true;
        }
        // under_maintenance → active | broken
        if ($current === EquipmentStatus::UNDER_MAINTENANCE &&
            in_array($target, [EquipmentStatus::ACTIVE, EquipmentStatus::BROKEN], true)) {
            return true;
        }
        // broken → under_repair
        if ($current === EquipmentStatus::BROKEN && $target === EquipmentStatus::UNDER_REPAIR) {
            return true;
        }
        // under_repair → active | broken
        if ($current === EquipmentStatus::UNDER_REPAIR &&
            in_array($target, [EquipmentStatus::ACTIVE, EquipmentStatus::BROKEN], true)) {
            return true;
        }
        // any active/operational → inactive
        if ($current instanceof EquipmentStatus && $current->isOperational() &&
            $target === EquipmentStatus::INACTIVE) {
            return true;
        }
        // any → retired (terminal state)
        if ($target === EquipmentStatus::RETIRED) {
            return true;
        }

        return false;
    }

    // ─── State Transitions ────────────────────────────────────────

    /**
     * Mark equipment as in_use when a loan begins (active → in_use).
     */
    public function markAsInUse(): void
    {
        $this->update(['status' => EquipmentStatus::IN_USE->value]);
    }

    /**
     * Return equipment to active after a completed loan (in_use → active).
     */
    public function markAsActive(): void
    {
        $this->update(['status' => EquipmentStatus::ACTIVE->value]);
    }

    /**
     * Reserve equipment when planned inside a mini-task (active → reserved).
     */
    public function markAsReserved(): void
    {
        $this->update(['status' => EquipmentStatus::RESERVED->value]);
    }

    /**
     * Request maintenance (any → maintenance_pending).
     */
    public function requestMaintenance(): void
    {
        $this->update(['status' => EquipmentStatus::MAINTENANCE_PENDING->value]);
    }

    /**
     * Start maintenance (maintenance_pending → under_maintenance).
     */
    public function startMaintenance(): void
    {
        $this->update(['status' => EquipmentStatus::UNDER_MAINTENANCE->value]);
    }

    /**
     * Mark as broken (under_maintenance/under_repair → broken).
     */
    public function markAsBroken(): void
    {
        $this->update(['status' => EquipmentStatus::BROKEN->value]);
    }

    /**
     * Start repair (broken → under_repair).
     */
    public function startRepair(): void
    {
        $this->update(['status' => EquipmentStatus::UNDER_REPAIR->value]);
    }

    /**
     * Complete maintenance/repair and return to active service.
     */
    public function completeMaintenance(): void
    {
        $this->update(['status' => EquipmentStatus::ACTIVE->value]);
    }

    /**
     * Retire equipment (terminal state).
     */
    public function retire(): void
    {
        $this->update(['status' => EquipmentStatus::RETIRED->value]);
    }

    /**
     * Set equipment as inactive (soft-disable).
     */
    public function deactivate(): void
    {
        $this->update(['status' => EquipmentStatus::INACTIVE->value]);
    }
}
