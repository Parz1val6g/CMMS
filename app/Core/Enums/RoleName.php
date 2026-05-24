<?php

namespace App\Core\Enums;

use App\Shared\Models\Role;
use Illuminate\Support\Collection;

class RoleName
{
    public const ADMIN = 'admin';
    public const MANAGER = 'manager';
    public const EQUIPMENT_MANAGER = 'equipment_manager';
    public const SUPERVISOR = 'supervisor';
    public const WORKER = 'worker';
    public const CLIENT = 'client';
    public const ENTITY = 'entidade';
    public const TASK_MANAGER = 'task_manager';
    public const MINI_TASK_MANAGER = 'mini_task_manager';
    public const WORK_LOG_MANAGER = 'work_log_manager';
    public const SECTOR_MANAGER = 'sector_manager';
    public const TICKET_MANAGER = 'ticket_manager';
    public const TEAM_MANAGER = 'team_manager';
    public const ATTENDANT = 'attendant';

    private static ?Collection $allRoles = null;

    /**
     * Resolve a role UUID by name, cached per-request.
     */
    public static function id(string $name): ?string
    {
        return self::all()->where('name', $name)->first()?->id;
    }

    /**
     * Return all roles as a collection, cached per-request.
     */
    public static function all(): Collection
    {
        if (self::$allRoles === null) {
            self::$allRoles = Role::all();
        }

        return self::$allRoles;
    }

    /**
     * Check if a role name exists in the database, cached per-request.
     */
    public static function exists(string $name): bool
    {
        return self::all()->where('name', $name)->isNotEmpty();
    }
}
