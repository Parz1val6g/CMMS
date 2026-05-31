<?php

return [
    'client' => [
        'sede_required' => 'A sede (primary location) is required for the client.',
    ],
    'task' => [
        'period_locked' => 'The task period cannot be changed in the current status.',
        'no_period_for_mini_task' => 'Cannot create mini-task: the parent task must have a defined execution period (start_date/end_date) first.',
        'start_date_before_service_order' => 'The start date must be within the service order period (:start – :end).',
        'end_date_after_service_order' => 'The end date must be within the service order period (:start – :end).',
    ],
    'service_order' => [
        'dates_conflict_tasks' => 'Cannot change dates: :count task(s) would fall outside the new period.',
    ],
];
