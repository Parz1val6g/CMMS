<?php

return [
    'equipment_status' => [
        'active' => 'Active',
        'in_use' => 'In Use',
        'maintenance_pending' => 'Maintenance Pending',
        'under_maintenance' => 'Under Maintenance',
        'broken' => 'Broken',
        'under_repair' => 'Under Repair',
        'reserved' => 'Reserved',
        'inactive' => 'Inactive',
        'retired' => 'Retired',
    ],

    'mini_task_status' => [
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'blocked' => 'Blocked',
        'cancelled' => 'Cancelled',
    ],

    'permission_action' => [
        'view' => 'View',
        'create' => 'Create',
        'update' => 'Update',
        'delete' => 'Delete',
        'change_role' => 'Change Role',
        'export' => 'Export',
        'import' => 'Import',
        'restore' => 'Restore',
        'force_delete' => 'Force Delete',
    ],

    'permission_resource' => [
        'users' => 'Users',
        'clients' => 'Clients',
        'locations' => 'Locations',
        'service_orders' => 'Service Orders',
        'service_types' => 'Service Types',
        'sessions' => 'Sessions',
        'login_histories' => 'Login Histories',
        'tasks' => 'Tasks',
        'mini_tasks' => 'Mini Tasks',
        'work_logs' => 'Work Logs',
        'equipments' => 'Equipments',
        'equipment_revisions' => 'Equipment Revisions',
        'sectors' => 'Sectors',
        'teams' => 'Teams',
        'workers' => 'Workers',
        'materials' => 'Materials',
        'units' => 'Units',
        'attachments' => 'Attachments',
        'roles' => 'Roles',
        'role_permissions' => 'Role Permissions',
        'profile' => 'Profile',
        'settings' => 'Settings',
    ],

    'priority' => [
        'low' => 'Low',
        'normal' => 'Normal',
        'high' => 'High',
        'urgent' => 'Urgent',
    ],

    'service_order_status' => [
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ],

    'system_status' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'suspended' => 'Suspended',
        'archived' => 'Archived',
    ],

    'task_status' => [
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'blocked' => 'Blocked',
        'cancelled' => 'Cancelled',
    ],

    'user_role' => [
        'admin' => 'Administrator',
        'manager' => 'Manager',
        'pending' => 'Pending Approval',
    ],

    'workflow_type' => [
        'regular' => 'Standard',
        'loan' => 'Loan',
    ],

    'work_log_status' => [
        'in_progress' => 'In Progress',
        'submitted' => 'Pending Approval',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ],
];
