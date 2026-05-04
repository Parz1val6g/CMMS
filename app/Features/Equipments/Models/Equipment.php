<?php

namespace App\Features\Equipments\Models;

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
     * Service orders that involve this equipment (REVISION, LOAN, etc.)
     */
    public function serviceOrders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class, 'equipment_id');
    }

    /**
     * Equipment revisions (approval history)
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(EquipmentRevision::class, 'equipment_id');
    }

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
     * Check if equipment can be loaned to clients
     */
    public function canBeLoanedToClient(): bool
    {
        return $this->is_loanable === true;
    }

    /**
     * Check if equipment is available for a new loan (active + loanable)
     */
    public function isAvailableForLoan(): bool
    {
        return $this->is_loanable === true && $this->status === 'active';
    }

    /**
     * Mark equipment as reserved (pending checkout)
     */
    public function markAsReserved(): void
    {
        $this->update(['status' => 'reserved']);
    }
}
