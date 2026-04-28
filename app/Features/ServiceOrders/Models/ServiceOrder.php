<?php
namespace App\Features\ServiceOrders\Models;
use App\Core\Traits\Base;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

use App\Features\Clients\Models\Client;
use App\Shared\Models\User;
use App\Features\Locations\Models\Location;
use App\Features\ServiceTypes\Models\ServiceType;
use App\Features\Tasks\Models\Task;
use App\Shared\Models\Attachment;

class ServiceOrder extends Model
{
    use Base;
    protected $fillable = [
        'process',
        'client_id',
        'manager_id',
        'location_id',
        'service_type_id',
        'priority',
        'execution_date',
        'status',
        'photo_path',
    ];
    protected $casts = [
        'execution_date' => 'date',
    ];
    protected $appends = ['photo_url'];

    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->photo_path) return null;
        return Storage::disk('public')->url($this->photo_path);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
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

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }
}