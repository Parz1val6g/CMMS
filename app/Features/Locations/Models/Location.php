<?php

namespace App\Features\Locations\Models;

use App\Core\Traits\Base;
use Illuminate\Database\Eloquent\Model;

use App\Shared\Models\Parish;
use App\Features\ServiceOrders\Models\ServiceOrder;

class Location extends Model
{
    use Base;

    protected $fillable = [
        'parish_id',
        'postal_code',
        'street_address',
        'landmark',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function parish(){
        return $this->belongsTo(Parish::class);
    }

    public function serviceOrders(){
        return $this->hasMany(ServiceOrder::class);
    }
}