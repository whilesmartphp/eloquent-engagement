<?php

namespace Whilesmart\Engagement\Facades;

use Illuminate\Support\Facades\Facade;
use Whilesmart\Engagement\EngagementManager;

/**
 * @method static \Whilesmart\Engagement\Models\EngagementEvent record(?\Illuminate\Database\Eloquent\Model $subject, string $name, array $metadata = [], ?\Carbon\CarbonInterface $occurredAt = null)
 * @method static void registerMetricProvider(string|\Whilesmart\Engagement\Contracts\MetricProvider $provider)
 * @method static \Whilesmart\Engagement\Contracts\MetricProvider[] metricProviders()
 * @method static array report(\Whilesmart\Engagement\Support\Period $period)
 *
 * @see EngagementManager
 */
class Engagement extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return EngagementManager::class;
    }
}
