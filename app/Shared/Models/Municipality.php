<?php

namespace App\Shared\Models;

use App\Core\Traits\Base;
use Illuminate\Database\Eloquent\Model;

class Municipality extends Model
{
    use Base;

    protected $fillable = [
        'name',
        'district_id'
    ];

    public function district()
    {
        return $this->belongsTo(District::class);
    }
    public function parishes()
    {
        return $this->hasMany(Parish::class);
    }
}
