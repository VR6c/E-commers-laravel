<?php

return [
    // Log API requests slower than this many milliseconds.
    'slow_request_ms' => (int) env('PERF_SLOW_REQUEST_MS', 500),

    // Log individual SQL queries slower than this many milliseconds.
    'slow_query_ms' => (int) env('PERF_SLOW_QUERY_MS', 100),

    // Flag requests that run more than this many queries (N+1 hint).
    'max_queries_per_request' => (int) env('PERF_MAX_QUERIES', 25),

    // Compress responses in PHP. Turn off (PERF_GZIP=false) on hosts that compress for you.
    'gzip' => (bool) env('PERF_GZIP', true),

    // Cache store used by the strict throttle on auth routes. Null = the default store.
    // Must be shared across requests (e.g. "database"); set PERF_THROTTLE_STORE=database on serverless hosts.
    'throttle_store' => env('PERF_THROTTLE_STORE'),

    'log_channel' => env('PERF_LOG_CHANNEL', 'daily'),
];
