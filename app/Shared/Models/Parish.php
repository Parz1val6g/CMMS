<?php

namespace App\Shared\Models;

use App\Core\Traits\Base;
use Illuminate\Database\Eloquent\Model;

use App\Features\Locations\Models\Location;

class Parish extends Model
{
    use Base;

    protected $fillable = [
        'name',
        'municipality_id'
    ];

    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }
    public function locations()
    {
        return $this->hasMany(Location::class);
    }
}
