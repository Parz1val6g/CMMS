<?php

namespace App\Shared\Models;

use App\Core\Traits\Base;
use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    use Base;

    protected $fillable = [
        'key',
        'value',
        'section',
    ];

    protected $casts = [
        'value' => 'array',
    ];

}
