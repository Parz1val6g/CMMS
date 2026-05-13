<?php

namespace App\Shared\Models;

use App\Core\Traits\Base;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;

class CostHistory extends Model
{
    use Base;

    protected $table = 'cost_histories';

    protected $fillable = [
        'entity_type',
        'entity_id',
        'cost_per_hour',
        'changed_by',
        'effective_from',
        'effective_until',
    ];

    protected $casts = [
        'cost_per_hour' => 'decimal:2',
        'effective_from' => 'datetime',
        'effective_until' => 'datetime',
    ];

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('effective_until');
    }

    public function scopeEffectiveAt(Builder $query, string $date): Builder
    {
        return $query->where('effective_from', '<=', $date)
            ->where(function (Builder $q) use ($date) {
                $q->whereNull('effective_until')
                  ->orWhere('effective_until', '>', $date);
            });
    }
}