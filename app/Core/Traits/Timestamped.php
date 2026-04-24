<?php

namespace App\Core\Traits;

trait Timestamped
{
    public function getCreatedAtFormatted(): string
    {
        return $this->created_at?->format('Y-m-d H:i:s') ?? '';
    }

    public function getUpdatedAtFormatted(): string
    {
        return $this->updated_at?->format('Y-m-d H:i:s') ?? '';
    }

    public function getDeletedAtFormatted(): string
    {
        return $this->deleted_at?->format('Y-m-d H:i:s') ?? '';
    }

    public function isRecent(int $minutes = 5): bool
    {
        return $this->created_at?->diffInMinutes(now()) < $minutes;
    }
}
