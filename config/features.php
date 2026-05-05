<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Route-level gating via EnsureFeatureIsEnabled middleware.
    | Set to false to disable a feature — the middleware will return 404.
    |
    */
    'service_orders'   => env('FEATURE_SERVICE_ORDERS', true),
    'equipment_loan'   => env('FEATURE_EQUIPMENT_LOAN', true),
    'analytics'        => env('FEATURE_ANALYTICS', true),
    'notifications'    => env('FEATURE_NOTIFICATIONS', true),
];
