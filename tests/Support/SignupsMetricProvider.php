<?php

namespace Tests\Support;

use Whilesmart\Engagement\Contracts\MetricProvider;
use Whilesmart\Engagement\Support\Metric;
use Whilesmart\Engagement\Support\Period;

/**
 * Stands in for a host-defined provider: it reports a metric the package knows
 * nothing about, proving providers drive the report rather than the package.
 */
class SignupsMetricProvider implements MetricProvider
{
    public function key(): string
    {
        return 'members';
    }

    public function label(): string
    {
        return 'Members';
    }

    public function metrics(Period $period): array
    {
        $count = Member::query()
            ->whereBetween('created_at', [$period->start, $period->end])
            ->count();

        return [
            Metric::count('total_members', 'Total members', Member::query()->count()),
            Metric::count('new_members', 'New members', $count),
        ];
    }
}
