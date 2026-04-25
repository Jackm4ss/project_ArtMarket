<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Realtime Delivery Driver
    |--------------------------------------------------------------------------
    |
    | Database rows remain the source of truth. The driver only decides how
    | clients receive fresh state. Shared hosting starts with SSE/polling,
    | then VPS can switch to Reverb/WebSocket through REALTIME_DRIVER.
    |
    */

    'driver' => env('REALTIME_DRIVER', 'sse'),

    'polling_interval_seconds' => (int) env('REALTIME_POLLING_INTERVAL_SECONDS', 10),

    'sse' => [
        'max_execution_seconds' => (int) env('REALTIME_SSE_MAX_EXECUTION_SECONDS', 55),
        'heartbeat_seconds' => (int) env('REALTIME_SSE_HEARTBEAT_SECONDS', 15),
        'retry_milliseconds' => (int) env('REALTIME_SSE_RETRY_MILLISECONDS', 1000),
        'reconnect_backoff_seconds' => [1, 2, 4, 8, 30],
        'hidden_tab_close_seconds' => (int) env('REALTIME_HIDDEN_TAB_CLOSE_SECONDS', 60),
    ],
];
