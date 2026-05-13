<?php

namespace App\Features\Equipments\Models;

use App\Core\Traits\Base;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CountingType extends Model
{
    use Base;

    protected $table = 'counting_types';

    protected $fillable = [
        'name',
        'value',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function equipments(): HasMany
    {
        return $this->hasMany(Equipment::class, 'counting_type_id');
    }
}
