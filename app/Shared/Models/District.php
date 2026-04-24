<?php

namespace App\Shared\Models;

use App\Core\Traits\Base;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use Base;

    protected $fillable = ['name'];

    public function municipalities()
    {
        return $this->hasMany(Municipality::class);
    }
}
