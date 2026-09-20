<?php

namespace Whilesmart\Engagement\Contracts;

/**
 * A metric provider whose figures change when the report is scoped to one client.
 *
 * Implement this only when the provider reads Period::$clientKey. Most providers measure
 * the product as a whole and return the same numbers whichever client sent the traffic.
 * The report states this per group.
 */
interface ClientScopedMetricProvider extends MetricProvider {}
