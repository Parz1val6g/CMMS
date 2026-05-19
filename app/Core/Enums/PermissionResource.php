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
    case LOAN_ORDERS = 'loan_orders';
    case ENTITIES = 'entities';
    case PROFILE = 'profile';
    case SETTINGS = 'settings';
    case EQUIPMENT_TYPES = 'equipment_types';
    case COUNTING_TYPES = 'counting_types';
    case TICKETS = 'tickets';

    public function label(): string
    {
        return match ($this) {
            self::USERS             => __('enums.permission_resource.users'),
            self::CLIENTS           => __('enums.permission_resource.clients'),
            self::LOCATIONS         => __('enums.permission_resource.locations'),
            self::SERVICE_ORDERS    => __('enums.permission_resource.service_orders'),
            self::SERVICE_TYPES     => __('enums.permission_resource.service_types'),
            self::SESSIONS          => __('enums.permission_resource.sessions'),
            self::LOGIN_HISTORIES   => __('enums.permission_resource.login_histories'),
            self::TASKS             => __('enums.permission_resource.tasks'),
            self::MINI_TASKS        => __('enums.permission_resource.mini_tasks'),
            self::WORK_LOGS         => __('enums.permission_resource.work_logs'),
            self::EQUIPMENTS        => __('enums.permission_resource.equipments'),
            self::EQUIPMENT_REVISIONS => __('enums.permission_resource.equipment_revisions'),
            self::SECTORS           => __('enums.permission_resource.sectors'),
            self::TEAMS             => __('enums.permission_resource.teams'),
            self::WORKERS           => __('enums.permission_resource.workers'),
            self::MATERIALS         => __('enums.permission_resource.materials'),
            self::UNITS             => __('enums.permission_resource.units'),
            self::ATTACHMENTS       => __('enums.permission_resource.attachments'),
            self::ROLES             => __('enums.permission_resource.roles'),
            self::ROLE_PERMISSIONS  => __('enums.permission_resource.role_permissions'),
            self::LOAN_ORDERS       => __('enums.permission_resource.loan_orders'),
            self::ENTITIES          => __('enums.permission_resource.entities'),
            self::PROFILE           => __('enums.permission_resource.profile'),
            self::SETTINGS          => __('enums.permission_resource.settings'),
            self::EQUIPMENT_TYPES   => __('enums.permission_resource.equipment_types'),
            self::COUNTING_TYPES    => __('enums.permission_resource.counting_types'),
            self::TICKETS           => __('enums.permission_resource.tickets'),
        };
    }
}
