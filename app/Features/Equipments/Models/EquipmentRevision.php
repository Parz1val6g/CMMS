<?php

namespace App\Features\Equipments\Models;

use App\Core\Traits\Base;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Shared\Models\User;

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

    /**
     * Check if revision is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if revision is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if revision is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
