<?php

namespace App\Features\Equipments\Models;

use App\Core\Traits\Base;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EquipmentType extends Model
{
    use Base;

    protected $table = 'equipment_types';

    protected $fillable = [
        'name',
        'category',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function equipments(): HasMany
    {
        return $this->hasMany(Equipment::class, 'equipment_type_id');
    }
}
