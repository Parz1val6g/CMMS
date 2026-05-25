<?php

namespace App\Features\Equipments\Models;

use App\Core\Enums\EquipmentRevisionStatus;
use App\Core\Traits\Base;
use App\Shared\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentRevision extends Model
{
    use Base;

    protected $table = 'equipment_revisions';

    protected $fillable = [
        'equipment_id',
        'status',
        'approved_by',
        'approved_at',
        'revision_date',
        'notes',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'revision_date' => 'datetime',
        'status' => EquipmentRevisionStatus::class,
    ];

    /**
     * The equipment being revised
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    /**
     * The user who approved this revision
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isApproved(): bool
    {
        return $this->status === EquipmentRevisionStatus::APPROVED;
    }

    public function isPending(): bool
    {
        return $this->status === EquipmentRevisionStatus::PENDING;
    }

    public function isRejected(): bool
    {
        return $this->status === EquipmentRevisionStatus::REJECTED;
    }
}
