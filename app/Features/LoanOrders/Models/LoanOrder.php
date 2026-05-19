<?php

namespace App\Features\LoanOrders\Models;

use App\Core\Enums\LoanOrderStatus;
use App\Core\Traits\Base;
use App\Core\Traits\HasAutoReference;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Features\Entities\Models\Entity;
use App\Features\Equipments\Models\Equipment;
use App\Features\Locations\Models\Location;
use App\Features\Tasks\Models\Task;
use App\Shared\Models\Parish;
use App\Shared\Models\User;

class LoanOrder extends Model
{
    use Base, HasAutoReference, SoftDeletes;

    protected function referenceInitials(): string
    {
        return 'EMP';
    }

    protected $fillable = [
        'reference',
        'entity_id',
        'manager_id',
        'location_id',
        'delivery_location_id',
        'migrated_from_so_id',
        'approved_by',
        'status',
        'description',
        'notes_checkout',
        'notes_return',
        'notes_cancel',
        'checked_out_at',
        'approved_at',
        'returned_at',
        'cancelled_at',
        'cancelled_by',
    ];

    protected $casts = [
        'status'         => LoanOrderStatus::class,
        'approved_at'    => 'datetime',
        'checked_out_at' => 'datetime',
        'returned_at'    => 'datetime',
        'cancelled_at'   => 'datetime',
    ];

    public function equipments()
    {
        return $this->belongsToMany(Equipment::class, 'equipment_loan_order')
            ->withPivot('start_date', 'end_date', 'needs_operator')
            ->withTimestamps();
    }

    public function tasks()
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function deliveryLocation()
    {
        return $this->belongsTo(Parish::class, 'delivery_location_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
}
