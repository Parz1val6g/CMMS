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
    case CANCEL = 'cancel';
    case COMPLETE = 'complete';
    case REJECT = 'reject';
    case ACTIVATE = 'activate';
    case APPROVE = 'approve';
    case CHECKOUT = 'checkout';
    case CONVERT = 'convert';
    case INITIATE_RETURN = 'initiate_return';
    case ASSIGN_WORKERS = 'assign_workers';
    case ASSIGN_MATERIALS = 'assign_materials';
    case ASSIGN_EQUIPMENT = 'assign_equipment';

    public function label(): string
    {
        return match ($this) {
            self::VIEW             => __('enums.permission_action.view'),
            self::CREATE           => __('enums.permission_action.create'),
            self::UPDATE           => __('enums.permission_action.update'),
            self::DELETE           => __('enums.permission_action.delete'),
            self::CHANGE_ROLE      => __('enums.permission_action.change_role'),
            self::EXPORT           => __('enums.permission_action.export'),
            self::IMPORT           => __('enums.permission_action.import'),
            self::RESTORE          => __('enums.permission_action.restore'),
            self::FORCE_DELETE     => __('enums.permission_action.force_delete'),
            self::CANCEL           => __('enums.permission_action.cancel'),
            self::COMPLETE         => __('enums.permission_action.complete'),
            self::REJECT           => __('enums.permission_action.reject'),
            self::ACTIVATE         => __('enums.permission_action.activate'),
            self::APPROVE          => __('enums.permission_action.approve'),
            self::CHECKOUT         => __('enums.permission_action.checkout'),
            self::CONVERT          => __('enums.permission_action.convert'),
            self::INITIATE_RETURN  => __('enums.permission_action.initiate_return'),
            self::ASSIGN_WORKERS   => __('enums.permission_action.assign_workers'),
            self::ASSIGN_MATERIALS => __('enums.permission_action.assign_materials'),
            self::ASSIGN_EQUIPMENT => __('enums.permission_action.assign_equipment'),
        };
    }
}
