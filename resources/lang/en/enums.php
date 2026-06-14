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
        'cancel' => 'Cancel',
        'complete' => 'Complete',
        'reject' => 'Reject',
        'activate' => 'Activate',
        'approve' => 'Approve',
        'checkout' => 'Check Out',
        'convert' => 'Convert',
        'initiate_return' => 'Initiate Return',
        'assign_workers' => 'Assign Workers',
        'assign_materials' => 'Assign Materials',
        'assign_equipment' => 'Assign Equipment',
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
        'tickets' => 'Tickets',
        'loan_orders' => 'Loan Orders',
        'entities' => 'Entities',
        'notifications' => 'Notifications',
        'equipment_types' => 'Equipment Types',
        'counting_types' => 'Counting Types',
        'service_order_categories' => 'Service Order Categories',
        'districts' => 'Districts',
        'municipalities' => 'Municipalities',
        'parishes' => 'Parishes',
    ],

    'priority' => [
        'low' => 'Low',
        'normal' => 'Normal',
        'high' => 'High',
        'urgent' => 'Urgent',
    ],

    'service_order_category' => [
        'repair'       => 'Repair',
        'maintenance'  => 'Maintenance',
        'installation' => 'Installation',
        'event'        => 'Event',
        'inspection'   => 'Inspection',
        'cleaning'     => 'Cleaning',
        'construction' => 'Construction',
        'emergency'    => 'Emergency',
    ],

    'service_order_status' => [
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'awaiting_approval' => 'Awaiting Approval',
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
        'awaiting_approval' => 'Awaiting Approval',
        'completed' => 'Completed',
        'blocked' => 'Blocked',
        'cancelled' => 'Cancelled',
    ],

    'user_role' => [
        'admin' => 'Administrator',
        'manager' => 'Manager',
        'pending' => 'Pending Approval',
    ],

    'role_name' => [
        'admin'             => 'Administrator',
        'manager'           => 'Manager',
        'equipment_manager'  => 'Equipment Manager',
        'supervisor'        => 'Supervisor',
        'worker'            => 'Worker',
        'client'            => 'Client',
        'entidade'          => 'Entity',
        'task_manager'      => 'Task Manager',
        'mini_task_manager'  => 'Mini-Task Manager',
        'work_log_manager'  => 'Work Log Manager',
        'sector_manager'    => 'Sector Manager',
        'attendant'         => 'Attendant',
        'ticket_manager'    => 'Ticket Manager',
        'team_manager'      => 'Team Manager',
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

    'ticket_status' => [
        'open' => 'Open',
        'in_progress' => 'In Progress',
        'converted' => 'Converted',
        'cancelled' => 'Cancelled',
    ],

    'ticket_priority' => [
        'low' => 'Low',
        'normal' => 'Normal',
        'high' => 'High',
        'urgent' => 'Urgent',
    ],

    'loan_order_status' => [
        'pending'     => 'Pending',
        'approved'    => 'Approved',
        'checked_out' => 'Checked Out',
        'returned'    => 'Returned',
        'cancelled'   => 'Cancelled',
    ],

    'entity_type' => [
        'municipal_council' => 'Municipal Council',
        'parish_council'    => 'Parish Council',
        'other'             => 'Other',
    ],
];
