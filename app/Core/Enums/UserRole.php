<?php

namespace App\Core\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case PENDING = 'pending';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => __('enums.user_role.admin'),
            self::MANAGER => __('enums.user_role.manager'),
            self::PENDING => __('enums.user_role.pending'),
        };
    }

    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    public function isManager(): bool
    {
        return $this === self::MANAGER;
    }

    public function isPending(): bool
    {
        return $this === self::PENDING;
    }
}
