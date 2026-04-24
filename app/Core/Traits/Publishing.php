<?php

namespace App\Core\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Publishing
{
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('published', false);
    }

    public function publish(): void
    {
        $this->update(['published' => true, 'published_at' => now()]);
    }

    public function unpublish(): void
    {
        $this->update(['published' => false]);
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function isDraft(): bool
    {
        return !$this->published;
    }
}
