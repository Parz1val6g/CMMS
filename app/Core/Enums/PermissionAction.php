<?php

namespace App\Core\Enums;

enum PermissionAction: string
{
    case VIEW = 'view';
    case CREATE = 'create';
    case UPDATE = 'update';
    case DELETE = 'delete';
    case CHANGE_ROLE = 'change_role';
    case EXPORT = 'export';
    case IMPORT = 'import';
    case RESTORE = 'restore';
    case FORCE_DELETE = 'force_delete';

    public function label(): string
    {
        return match ($this) {
            self::VIEW => 'View',
            self::CREATE => 'Create',
            self::UPDATE => 'Update',
            self::DELETE => 'Delete',
            self::CHANGE_ROLE => 'Change Role',
            self::EXPORT => 'Export',
            self::IMPORT => 'Import',
            self::RESTORE => 'Restore',
            self::FORCE_DELETE => 'Force Delete',
        };
    }
}
