<?php

namespace App\Core\Enums;

enum PermissionResource: string
{
    case USERS = 'users';
    case CLIENTS = 'clients';
    case LOCATIONS = 'locations';
    case SERVICE_ORDERS = 'service_orders';
    case SERVICE_TYPES = 'service_types';
    case SESSIONS = 'sessions';
    case LOGIN_HISTORIES = 'login_histories';
    case TASKS = 'tasks';
    case MINI_TASKS = 'mini_tasks';
    case WORK_LOGS = 'work_logs';
    case EQUIPMENTS = 'equipments';
    case EQUIPMENT_REVISIONS = 'equipment_revisions';
    case SECTORS = 'sectors';
    case TEAMS = 'teams';
    case WORKERS = 'workers';
    case MATERIALS = 'materials';
    case UNITS = 'units';
    case ATTACHMENTS = 'attachments';
    case ROLES = 'roles';
    case ROLE_PERMISSIONS = 'role_permissions';
    case PROFILE = 'profile';
    case SETTINGS = 'settings';

    public function label(): string
    {
        return match ($this) {
            self::USERS => 'Users',
            self::CLIENTS => 'Clients',
            self::LOCATIONS => 'Locations',
            self::SERVICE_ORDERS => 'Service Orders',
            self::SERVICE_TYPES => 'Service Types',
            self::SESSIONS => 'Sessions',
            self::LOGIN_HISTORIES => 'Login Histories',
            self::TASKS => 'Tasks',
            self::MINI_TASKS => 'Mini Tasks',
            self::WORK_LOGS => 'Work Logs',
            self::EQUIPMENTS => 'Equipments',
            self::EQUIPMENT_REVISIONS => 'Equipment Revisions',
            self::SECTORS => 'Sectors',
            self::TEAMS => 'Teams',
            self::WORKERS => 'Workers',
            self::MATERIALS => 'Materials',
            self::UNITS => 'Units',
            self::ATTACHMENTS => 'Attachments',
            self::ROLES => 'Roles',
            self::ROLE_PERMISSIONS => 'Role Permissions',
            self::PROFILE => 'Profile',
            self::SETTINGS => 'Settings',
        };
    }
}
