<?php

namespace Whilesmart\Engagement;

use Carbon\CarbonInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Whilesmart\Engagement\Contracts\ClientScopedMetricProvider;
use Whilesmart\Engagement\Contracts\MetricProvider;
use Whilesmart\Engagement\Models\EngagementEvent;
use Whilesmart\Engagement\Support\ClientRegistry;
use Whilesmart\Engagement\Support\Period;

class EngagementManager
{
    /** @var array<int, class-string<MetricProvider>|MetricProvider> */
    protected array $providers = [];

    public function __construct(protected Container $container) {}

    /**
     * Record a usage event. $subject is whoever the event belongs to (a user
     * model), or null for anonymous/system events. The event name is the
     * host's own vocabulary; the package never enumerates valid names.
     */
    public function record(?Model $subject, string $name, array $metadata = [], ?CarbonInterface $occurredAt = null): EngagementEvent
    {
        $event = new EngagementEvent([
            'name' => $name,
            'metadata' => $metadata ?: null,
            'occurred_at' => $occurredAt ?? now(),
        ]);

        if ($subject !== null) {
            $event->subject()->associate($subject);
        }

        $event->save();

        return $event;
    }

    /**
     * @param  class-string<MetricProvider>|MetricProvider  $provider
     */
    public function registerMetricProvider(string|MetricProvider $provider): void
    {
        $this->providers[] = $provider;
    }

    /**
     * @return MetricProvider[]
     */
    public function metricProviders(): array
    {
        return array_map(
            fn ($provider) => $provider instanceof MetricProvider ? $provider : $this->container->make($provider),
            $this->providers
        );
    }

    /**
     * Build the full report: every provider's metrics for the period, grouped.
     *
     * @return array{clients: array<int, array{key: string, name: string}>, selected_client: string|null, period: array{start: string, end: string, granularity: string}, groups: array<int, array{key: string, label: string, client_scoped: bool, metrics: array}>}
     */
    public function report(Period $period): array
    {
        $groups = [];

        foreach ($this->metricProviders() as $provider) {
            $groups[] = [
                'key' => $provider->key(),
                'label' => $provider->label(),
                'client_scoped' => $provider instanceof ClientScopedMetricProvider,
                'metrics' => array_map(
                    fn ($metric) => $metric->toArray(),
                    $provider->metrics($period)
                ),
            ];
        }

        return [
            'clients' => $this->container->make(ClientRegistry::class)->publicClients(),
            'selected_client' => $period->clientKey,
            'period' => [
                'start' => $period->start->toIso8601String(),
                'end' => $period->end->toIso8601String(),
                'granularity' => $period->granularity,
            ],
            'groups' => $groups,
        ];
    }
}
