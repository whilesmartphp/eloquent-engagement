<?php

use Whilesmart\Engagement\Providers\EventMetricProvider;
use Whilesmart\Engagement\Providers\VisitorMetricProvider;

return [
    'register_routes' => env('ENGAGEMENT_REGISTER_ROUTES', true),
    'register_report_route' => env('ENGAGEMENT_REGISTER_REPORT_ROUTE', env('ENGAGEMENT_REGISTER_ROUTES', true)),
    'register_ingest_route' => env('ENGAGEMENT_REGISTER_INGEST_ROUTE', env('ENGAGEMENT_REGISTER_ROUTES', true)),
    'route_prefix' => env('ENGAGEMENT_ROUTE_PREFIX', 'api'),

    // The host secures the report endpoint. Engagement metrics are app-wide, so
    // this should be an admin-only stack in production (e.g. ['api', 'auth:sanctum', 'role:admin']).
    'route_middleware' => ['api', 'auth:sanctum'],
    'ingest_route_middleware' => ['api', 'throttle:60,1'],
    'site_key' => env('ENGAGEMENT_SITE_KEY'),
    'allowed_origins' => array_values(array_filter(array_map('trim', explode(',', env('ENGAGEMENT_ALLOWED_ORIGINS', ''))))),
    'max_batch_size' => 20,
    'clients' => [
        'default' => [
            'name' => env('ENGAGEMENT_CLIENT_NAME', 'Default'),
            'site_key' => env('ENGAGEMENT_SITE_KEY', ''),
            'allowed_origins' => array_values(array_filter(array_map('trim', explode(',', env('ENGAGEMENT_ALLOWED_ORIGINS', ''))))),
        ],
    ],

    'events_table' => env('ENGAGEMENT_EVENTS_TABLE', 'engagement_events'),

    // Metric providers the report aggregates. Each implements
    // Whilesmart\Engagement\Contracts\MetricProvider. The host appends its own
    // (users, transactions, ...); nothing about a specific feature lives here.
    'providers' => [
        EventMetricProvider::class,
        VisitorMetricProvider::class,
    ],
];
