<?php

namespace Whilesmart\Engagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Whilesmart\Engagement\Database\Factories\EngagementEventFactory;

class EngagementEvent extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return config('engagement.events_table', 'engagement_events');
    }

    /** Who the event is attributed to (usually a user). */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    protected static function newFactory(): EngagementEventFactory
    {
        return EngagementEventFactory::new();
    }
}
