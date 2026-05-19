<?php

namespace App\Features\Entities\Models;

use App\Core\Enums\EntityType;
use App\Core\Traits\Base;
use App\Features\LoanOrders\Models\LoanOrder;
use App\Shared\Models\Parish;
use App\Shared\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Entity extends Model
{
    use Base, SoftDeletes;

    protected $table = 'entities';

    protected $fillable = [
        'user_id',
        'entity_type',
        'nif',
        'name',
        'phone',
        'location_id',
    ];

    protected $casts = [
        'entity_type' => EntityType::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function location()
    {
        return $this->belongsTo(Parish::class, 'location_id');
    }

    public function loanOrders()
    {
        return $this->hasMany(LoanOrder::class, 'entity_id');
    }
}
