<?php

namespace Whilesmart\Engagement\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Whilesmart\Engagement\Facades\Engagement;
use Whilesmart\Engagement\Models\EngagementEvent;

/**
 * Add to the model that engagement is attributed to (typically User) so it can
 * record its own events and read them back.
 */
trait RecordsEngagement
{
    public function engagementEvents(): MorphMany
    {
        return $this->morphMany(EngagementEvent::class, 'subject');
    }

    public function recordEngagement(string $name, array $metadata = []): EngagementEvent
    {
        return Engagement::record($this, $name, $metadata);
    }
}
