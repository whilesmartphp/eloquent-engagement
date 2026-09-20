<?php

namespace Whilesmart\Engagement\Providers;

use Whilesmart\Engagement\Contracts\ClientScopedMetricProvider;
use Whilesmart\Engagement\Models\EngagementEvent;
use Whilesmart\Engagement\Support\Metric;
use Whilesmart\Engagement\Support\Period;

class VisitorMetricProvider implements ClientScopedMetricProvider
{
    public function key(): string
    {
        return 'visitors';
    }

    public function label(): string
    {
        return 'Visitors';
    }

    public function metrics(Period $period): array
    {
        $events = EngagementEvent::query()
            ->whereNotNull('visitor_id')
            ->when($period->clientKey, fn ($query, string $clientKey) => $query->where('client_key', $clientKey))
            ->whereBetween('occurred_at', [$period->start, $period->end])
            ->get(['visitor_id', 'session_id', 'name', 'source', 'utm_medium', 'utm_campaign', 'page_url', 'occurred_at']);

        $format = $period->bucketFormat();
        $visitorsByBucket = [];
        foreach ($events as $event) {
            $visitorsByBucket[$event->occurred_at->format($format)][$event->visitor_id] = true;
        }

        $series = array_map(fn ($bucket) => [
            'date' => $bucket->format($format),
            'value' => count($visitorsByBucket[$bucket->format($format)] ?? []),
        ], $period->buckets());

        return [
            Metric::count('unique_visitors', 'Unique visitors', $events->pluck('visitor_id')->unique()->count()),
            Metric::count('sessions', 'Sessions', $events->pluck('session_id')->filter()->unique()->count()),
            Metric::count('page_views', 'Page views', $events->where('name', 'page.view')->count()),
            Metric::series('visitors_over_time', 'Visitors over time', $series),
            Metric::ranking('top_sources', 'Top sources', $this->ranking($events->pluck('source')->filter()->all())),
            Metric::ranking('top_campaigns', 'Top campaigns', $this->ranking($events->pluck('utm_campaign')->filter()->all())),
            Metric::ranking('top_mediums', 'Top media', $this->ranking($events->pluck('utm_medium')->filter()->all())),
            Metric::ranking('top_pages', 'Top pages', $this->ranking($events->where('name', 'page.view')->pluck('page_url')->filter()->all())),
        ];
    }

    private function ranking(array $values): array
    {
        return collect($values)->countBy()->sortDesc()->take(8)
            ->map(fn (int $value, string $label) => compact('label', 'value'))
            ->values()->all();
    }
}
