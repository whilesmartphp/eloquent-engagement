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
            ->get(['visitor_id', 'session_id', 'name', 'source', 'utm_medium', 'utm_campaign', 'page_url', 'occurred_at', 'subject_id']);

        $format = $period->bucketFormat();
        $visitorsByBucket = [];
        foreach ($events as $event) {
            $visitorsByBucket[$event->occurred_at->format($format)][$event->visitor_id] = true;
        }

        $series = array_map(fn ($bucket) => [
            'date' => $bucket->format($format),
            'value' => count($visitorsByBucket[$bucket->format($format)] ?? []),
        ], $period->buckets());

        $identified = $events->groupBy('visitor_id')->filter(fn ($visits) => $visits->contains(fn ($event) => $event->subject_id !== null));

        return [
            Metric::count('unique_visitors', 'Unique visitors', $events->pluck('visitor_id')->unique()->count()),
            Metric::count('sessions', 'Sessions', $events->pluck('session_id')->filter()->unique()->count()),
            Metric::count('page_views', 'Page views', $events->where('name', 'page.view')->count()),
            Metric::series('visitors_over_time', 'Visitors over time', $series),
            Metric::ranking('top_sources', 'Top sources', $this->ranking($events->pluck('source')->filter()->all())),
            Metric::ranking('top_campaigns', 'Top campaigns', $this->ranking($events->pluck('utm_campaign')->filter()->all())),
            Metric::ranking('top_mediums', 'Top media', $this->ranking($events->pluck('utm_medium')->filter()->all())),
            Metric::ranking('top_pages', 'Top pages', $this->ranking($events->where('name', 'page.view')->pluck('page_url')->filter()->all())),
            Metric::count('identified_visitors', 'Visitors who signed in', $identified->count()),
            Metric::ranking('identified_by_source', 'Sign-ins by first source', $this->ranking($this->firstSources($identified->keys()->all()))),
        ];
    }

    /**
     * Where each visitor first arrived from, read across all time rather than the period, so a
     * visitor who came from a campaign before the period still counts for that campaign.
     *
     * @param  array<int, string>  $visitorIds
     * @return array<int, string>
     */
    private function firstSources(array $visitorIds): array
    {
        if ($visitorIds === []) {
            return [];
        }
        $first = EngagementEvent::query()->whereIn('visitor_id', $visitorIds)->whereNotNull('source')
            ->groupBy('visitor_id')->selectRaw('min(id) as id');

        return EngagementEvent::query()->whereIn('id', $first)->pluck('source', 'visitor_id')->all()
            + array_fill_keys($visitorIds, 'direct');
    }

    private function ranking(array $values): array
    {
        return collect($values)->countBy()->sortDesc()->take(8)
            ->map(fn (int $value, string $label) => compact('label', 'value'))
            ->values()->all();
    }
}
