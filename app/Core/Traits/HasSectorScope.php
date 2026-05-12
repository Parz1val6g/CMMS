<?php

namespace App\Core\Traits;

use App\Features\Sectors\Models\Sector;
use App\Shared\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait HasSectorScope
{
    /**
     * Scope query to records belonging to sectors managed by the given user.
     * For sector_manager role — filters by sectors where user is head.
     */
    public function scopeForSectorManager(Builder $query, User $user): Builder
    {
        return $query->whereIn('sector_id', Sector::where('head_id', $user->id)->pluck('id'));
    }
}
