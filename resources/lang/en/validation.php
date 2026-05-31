<?php

return [
    'client' => [
        'sede_required' => 'A sede (primary location) is required for the client.',
    ],
    'task' => [
        'period_locked' => 'The task period cannot be changed in the current status.',
        'no_period_for_mini_task' => 'Cannot create mini-task: the parent task must have a defined execution period (start_date/end_date) first.',
    ],
];
