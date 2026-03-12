<?php

return [
    'max_enabled' => env('METRICS_MAX_ENABLED', 5),
    'retention_days' => env('METRICS_RETENTION_DAYS', 40),
    'slow_route_threshold_ms' => env('METRICS_SLOW_ROUTE_THRESHOLD_MS', 500),
    'cache_ttl_seconds' => env('METRICS_CACHE_TTL_SECONDS', 30),

    'features' => [
        [
            'key' => 'http_requests',
            'name' => 'HTTP Requests',
            'description' => 'Request counts, status codes, and latency.',
            'default_enabled' => true,
            'sample_rate' => 1.0,
        ],
        [
            'key' => 'exceptions',
            'name' => 'Exceptions',
            'description' => 'Exception counts by class/message.',
            'default_enabled' => true,
            'sample_rate' => 1.0,
        ],
        [
            'key' => 'db_queries',
            'name' => 'Database Queries',
            'description' => 'Total query time and slow query tracking.',
            'default_enabled' => true,
            'sample_rate' => 1.0,
        ],
        [
            'key' => 'queue',
            'name' => 'Queue Jobs',
            'description' => 'Job throughput and failures.',
            'default_enabled' => true,
            'sample_rate' => 1.0,
        ],
        [
            'key' => 'slow_routes',
            'name' => 'Slow Routes',
            'description' => 'Top routes by latency.',
            'default_enabled' => true,
            'sample_rate' => 1.0,
        ],
        [
            'key' => 'cache',
            'name' => 'Cache',
            'description' => 'Cache hit/miss rate.',
            'default_enabled' => false,
            'sample_rate' => 1.0,
        ],
        [
            'key' => 'mail',
            'name' => 'Mail',
            'description' => 'Mail sends and failures.',
            'default_enabled' => false,
            'sample_rate' => 1.0,
        ],
        [
            'key' => 'auth',
            'name' => 'Auth',
            'description' => 'Login failures and rate limit hits.',
            'default_enabled' => false,
            'sample_rate' => 1.0,
        ],
    ],
];
