<?php

namespace Whilesmart\Engagement\Support;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * An inclusive date range plus the bucket size used when a metric is rendered
 * as a time series. Providers read $start/$end to scope queries and call
 * eachBucket() to lay out series points.
 */
class Period
{
    public readonly CarbonImmutable $start;

    public readonly CarbonImmutable $end;

    public function __construct(CarbonInterface $start, CarbonInterface $end, public readonly string $granularity = 'day')
    {
        $this->start = CarbonImmutable::instance($start)->startOfDay();
        $this->end = CarbonImmutable::instance($end)->endOfDay();
    }

    public static function lastDays(int $days, string $granularity = 'day'): self
    {
        $end = CarbonImmutable::now();

        return new self($end->subDays(max(0, $days - 1)), $end, $granularity);
    }

    /**
     * Walk the period one bucket at a time, yielding the start of each bucket.
     * Useful for building a zero-filled series so gaps render as 0, not holes.
     *
     * @return CarbonImmutable[]
     */
    public function buckets(): array
    {
        $step = match ($this->granularity) {
            'month' => 'addMonth',
            'week' => 'addWeek',
            default => 'addDay',
        };

        $cursor = match ($this->granularity) {
            'month' => $this->start->startOfMonth(),
            'week' => $this->start->startOfWeek(),
            default => $this->start->startOfDay(),
        };

        $out = [];
        while ($cursor->lessThanOrEqualTo($this->end)) {
            $out[] = $cursor;
            $cursor = $cursor->{$step}();
        }

        return $out;
    }

    /** Date format that collapses a timestamp into its bucket key. */
    public function bucketFormat(): string
    {
        return match ($this->granularity) {
            'month' => 'Y-m',
            'week' => 'o-\WW',
            default => 'Y-m-d',
        };
    }
}
