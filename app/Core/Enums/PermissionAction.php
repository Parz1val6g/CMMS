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
            self::VIEW => __('enums.permission_action.view'),
            self::CREATE => __('enums.permission_action.create'),
            self::UPDATE => __('enums.permission_action.update'),
            self::DELETE => __('enums.permission_action.delete'),
            self::CHANGE_ROLE => __('enums.permission_action.change_role'),
            self::EXPORT => __('enums.permission_action.export'),
            self::IMPORT => __('enums.permission_action.import'),
            self::RESTORE => __('enums.permission_action.restore'),
            self::FORCE_DELETE => __('enums.permission_action.force_delete'),
        };
    }
}
