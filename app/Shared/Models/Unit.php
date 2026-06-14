<?php

namespace App\Shared\Models;

use App\Core\Traits\Base;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use Base;

    protected $fillable = [
        'name',
        'abbreviation',
        'step',
    ];
}
