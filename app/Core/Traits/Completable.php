<?php

namespace App\Core\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Completable
{
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereNotNull('completed_at');
    }

    public function scopeIncomplete(Builder $query): Builder
    {
        return $query->whereNull('completed_at');
    }

    public function markComplete(): void
    {
        $this->update(['completed_at' => now()]);
    }

    public function markIncomplete(): void
    {
        $this->update(['completed_at' => null]);
    }

    public function isComplete(): bool
    {
        return $this->completed_at !== null;
    }

    public function isIncomplete(): bool
    {
        return $this->completed_at === null;
    }

    public function getCompletionPercentage(): int
    {
        return $this->isComplete() ? 100 : 0;
    }
}
