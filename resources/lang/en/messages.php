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
        'ticket' => [
            'cannot_update_terminal' => 'Cannot update a converted or cancelled ticket.',
            'already_terminal' => 'This ticket is already in a terminal state (converted or cancelled).',
        ],
        'loan_orders' => [
            'must_be_pending_to_approve' => 'Loan order must be in PENDING status to approve.',
            'must_be_approved_to_checkout' => 'Loan order must be APPROVED to checkout.',
            'must_be_checked_out' => 'Loan order must be CHECKED_OUT to return.',
            'cannot_cancel_status' => 'Cannot cancel a loan order in :status status. Only PENDING loans can be cancelled.',
            'equipment_unavailable' => 'Equipment :ref is not available for the selected dates.',
            'return_task_exists' => 'A return task already exists for this loan order.',
            'checkout_must_complete' => 'The equipment checkout task must be completed before initiating return.',
            'cannot_delete' => 'Cannot delete a loan order in :status status.',
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
        'tickets' => [
            'col_description' => 'Description',
            'col_client' => 'Client',
            'col_priority' => 'Priority',
            'col_status' => 'Status',
            'col_created' => 'Created',
            'filter_status' => 'Status',
            'filter_priority' => 'Priority',
        ],
        'loan_orders' => [
            'col_reference' => 'Reference',
            'col_client'    => 'Client',
            'col_manager'   => 'Manager',
            'col_status'    => 'Status',
            'col_created'   => 'Created',
        ],
    ],
    'task_names' => [
        'equipment_loan' => 'Equipment Loan',
        'equipment_return' => 'Equipment Return',
    ],
];
