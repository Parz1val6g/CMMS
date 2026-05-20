<?php
namespace App\Features\ServiceOrders\Models;
use App\Core\Enums\Priority;
use App\Core\Enums\ServiceOrderStatus;
use App\Core\Traits\Base;
use App\Core\Traits\HasAutoReference;
use App\Core\Traits\LogsAuditTrail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

use App\Features\Clients\Models\Client;
use App\Features\Clients\Models\ClientLocation;
use App\Shared\Models\User;
use App\Features\Locations\Models\Location;
use App\Features\ServiceTypes\Models\ServiceType;
use App\Features\Sectors\Models\Sector;
use App\Features\Tasks\Models\Task;
use App\Shared\Models\Attachment;

class ServiceOrder extends Model
{
    use Base, HasAutoReference, LogsAuditTrail;

    protected function referenceColumn(): string
    {
        return 'process';
    }

    protected function referenceInitials(): string
    {
        return 'OS';
    }
    protected $fillable = [
        'process',
        'client_id',
        'client_location_id',
        'manager_id',
        'location_id',
        'service_type_id',
        'migrated_to_loan_id',
        'priority',
        'execution_date',
        'status',
        'photo_path',
        'description',
        'start_notified_at',
    ];
    protected $casts = [
        'execution_date' => 'date',
        'priority' => Priority::class,
        'status' => ServiceOrderStatus::class,
        'start_notified_at' => 'datetime',
    ];

    public function migratedToLoan()
    {
        return $this->belongsTo(\App\Features\LoanOrders\Models\LoanOrder::class, 'migrated_to_loan_id');
    }
    protected $appends = ['photo_url'];

    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->photo_path)
            return null;
        return Storage::disk('public')->url($this->photo_path);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function clientLocation()
    {
        return $this->belongsTo(ClientLocation::class);
    }
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function sectors()
    {
        return $this->belongsToMany(Sector::class, 'service_order_sector', 'service_order_id', 'sector_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}