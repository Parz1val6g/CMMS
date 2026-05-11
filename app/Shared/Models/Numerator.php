<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Model;

class Numerator extends Model
{
    protected $fillable = [
        'entity_table',
        'year',
        'current_value',
        'last_generated',
    ];

    protected $casts = [
        'year' => 'integer',
        'current_value' => 'integer',
        'last_generated' => 'datetime',
    ];
}
