<?php
namespace App\Features\ServiceOrders\Models;
use App\Core\Enums\Priority;
use App\Core\Enums\ServiceOrderStatus;
use App\Features\ServiceOrderCategories\Models\ServiceOrderCategory;
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
use Illuminate\Support\Facades\DB;

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
        'title',
        'client_id',
        'client_location_id',
        'manager_id',
        'created_by',
        'location_id',
        'migrated_to_loan_id',
        'priority',
        'category_id',
        'start_date',
        'end_date',
        'status',
        'photo_path',
        'description',
        'start_notified_at',
    ];
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
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

    public function category()
    {
        return $this->belongsTo(ServiceOrderCategory::class, 'category_id');
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
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
    public function sectors()
    {
        return $this->belongsToMany(Sector::class, 'service_order_sector', 'service_order_id', 'sector_id');
    }

    public function serviceTypes()
    {
        return $this->belongsToMany(ServiceType::class, 'service_order_sector_service_type', 'service_order_id', 'service_type_id')
            ->withPivot('sector_id');
    }

    public function serviceTypesBySector(): array
    {
        $rows = DB::table('service_order_sector_service_type as sost')
            ->join('service_types as st', 'st.id', '=', 'sost.service_type_id')
            ->where('sost.service_order_id', $this->id)
            ->select('sost.sector_id', 'st.id as service_type_id', 'st.name as service_type_name', 'sost.priority')
            ->get();

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row->sector_id][] = ['id' => $row->service_type_id, 'name' => $row->service_type_name, 'priority' => $row->priority];
        }
        return $grouped;
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