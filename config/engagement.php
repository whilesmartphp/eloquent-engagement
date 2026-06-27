<?php

use Whilesmart\Engagement\Providers\EventMetricProvider;

return [
    'register_routes' => env('ENGAGEMENT_REGISTER_ROUTES', true),
    'route_prefix' => env('ENGAGEMENT_ROUTE_PREFIX', 'api'),

    // The host secures the report endpoint. Engagement metrics are app-wide, so
    // this should be an admin-only stack in production (e.g. ['api', 'auth:sanctum', 'role:admin']).
    'route_middleware' => ['api', 'auth:sanctum'],

    'events_table' => env('ENGAGEMENT_EVENTS_TABLE', 'engagement_events'),

    // Metric providers the report aggregates. Each implements
    // Whilesmart\Engagement\Contracts\MetricProvider. The host appends its own
    // (users, transactions, ...); nothing about a specific feature lives here.
    'providers' => [
        EventMetricProvider::class,
    ],
];
