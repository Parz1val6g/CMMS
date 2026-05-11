<?php

namespace App\Features\Clients\Models;

use App\Core\Traits\Base;
use App\Features\Locations\Models\Location;
use App\Features\ServiceOrders\Models\ServiceOrder;
use Illuminate\Database\Eloquent\Model;

class ClientLocation extends Model
{
    use Base;

    protected $fillable = [
        'client_id',
        'location_id',
        'name',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function serviceOrders()
    {
        return $this->hasMany(ServiceOrder::class);
    }
}
