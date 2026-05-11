<?php

return [
    'http' => [
        401 => 'Authentication required',
        403 => 'Access denied',
        404 => 'Resource not found',
        422 => 'Validation failed',
        500 => 'An error occurred',
    ],
    'services' => [
        'service_order' => [
            'equipment_not_available' => 'Equipment is not available for loan. It must be active, loanable, and not overdue for revision.',
            'cannot_update_completed' => 'Cannot update a completed or cancelled service order.',
            'cannot_cancel_completed' => 'Cannot cancel an already completed service order.',
            'return_only_for_loan' => 'Initiate return is only valid for loan workflows.',
            'return_task_exists' => 'A return task already exists for this service order.',
            'checkout_must_complete' => 'Equipment checkout task must be completed before initiating return.',
            'already_completed' => 'This service order is already completed.',
            'incomplete_tasks' => 'Manager Approval Denied: Not all tasks are completed yet.',
        ],
        'equipment' => [
            'invalid_status' => "Invalid status value ':value'.",
            'cannot_transition' => "Cannot transition equipment from ':from' to ':to'.",
            'invalid_transition' => 'Invalid equipment state transition.',
        ],
        'notifications' => [
            'new_order_title' => 'New Service Order Created',
            'new_order_message' => 'Service Order :process has been created and assigned to you.',
        ],
    ],
    'controllers' => [
        'service_orders' => [
            'col_process' => 'Process',
            'col_description' => 'Description',
            'col_client' => 'Client',
            'col_priority' => 'Priority',
            'col_status' => 'Status',
            'col_created' => 'Created',
            'filter_search' => 'Search',
            'filter_status' => 'Status',
            'filter_priority' => 'Priority',
            'search_placeholder' => 'Search process...',
        ],
    ],
    'task_names' => [
        'equipment_loan' => 'Equipment Loan',
        'equipment_return' => 'Equipment Return',
    ],
];
