<?php

namespace Whilesmart\Engagement\Contracts;

use Whilesmart\Engagement\Support\Metric;
use Whilesmart\Engagement\Support\Period;

/**
 * Implemented by any host feature that wants to surface figures in the
 * engagement report. The package collects every registered provider and asks
 * each for its metrics over the period, so the set of metrics is owned by the
 * host, not hard-coded here.
 */
interface MetricProvider
{
    /** Stable machine key grouping this provider's metrics (e.g. "users"). */
    public function key(): string;

    /** Human label for the group (e.g. "Users"). */
    public function label(): string;

    /**
     * @return Metric[]
     */
    public function metrics(Period $period): array;
}
