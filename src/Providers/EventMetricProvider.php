<?php

namespace Whilesmart\Engagement\Providers;

use Whilesmart\Engagement\Contracts\MetricProvider;
use Whilesmart\Engagement\Models\EngagementEvent;
use Whilesmart\Engagement\Support\Metric;
use Whilesmart\Engagement\Support\Period;

/**
 * Default provider over recorded engagement events: total events, distinct
 * active subjects, and an active-subjects series. Event names are whatever the
 * host recorded, so "features used" is never hard-coded.
 */
class EventMetricProvider implements MetricProvider
{
    public function key(): string
    {
        return 'activity';
    }

    public function label(): string
    {
        return 'Activity';
    }

    public function metrics(Period $period): array
    {
        $events = EngagementEvent::query()
            ->whereBetween('occurred_at', [$period->start, $period->end])
            ->get(['subject_type', 'subject_id', 'name', 'occurred_at']);

        $format = $period->bucketFormat();

        $subjectsPerBucket = [];
        $allSubjects = [];
        $eventNameCounts = [];

        foreach ($events as $event) {
            $bucket = $event->occurred_at->format($format);
            $subjectKey = $event->subject_id !== null ? $event->subject_type.':'.$event->subject_id : null;

            if ($subjectKey !== null) {
                $subjectsPerBucket[$bucket][$subjectKey] = true;
                $allSubjects[$subjectKey] = true;
            }

            $eventNameCounts[$event->name] = ($eventNameCounts[$event->name] ?? 0) + 1;
        }

        $series = [];
        foreach ($period->buckets() as $bucketStart) {
            $bucket = $bucketStart->format($format);
            $series[] = [
                'date' => $bucket,
                'value' => count($subjectsPerBucket[$bucket] ?? []),
            ];
        }

        arsort($eventNameCounts);

        return [
            Metric::count('total_events', 'Total events', $events->count()),
            Metric::count('active_subjects', 'Active users', count($allSubjects)),
            Metric::series('active_subjects_series', 'Active users over time', $series),
            Metric::ranking('top_features', 'Most used features', $this->topFeatures($eventNameCounts)),
        ];
    }

    /**
     * @param  array<string, int>  $counts
     * @return array<int, array{label: string, value: int}>
     */
    private function topFeatures(array $counts): array
    {
        $rows = [];
        foreach (array_slice($counts, 0, 8, true) as $name => $count) {
            $rows[] = ['label' => $name, 'value' => $count];
        }

        return $rows;
    }
}
