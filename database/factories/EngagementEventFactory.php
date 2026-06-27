<?php

namespace Whilesmart\Engagement\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Whilesmart\Engagement\Models\EngagementEvent;

class EngagementEventFactory extends Factory
{
    protected $model = EngagementEvent::class;

    public function definition(): array
    {
        return [
            'subject_type' => null,
            'subject_id' => null,
            'name' => $this->faker->randomElement(['session.start', 'transaction.created', 'report.viewed']),
            'metadata' => null,
            'occurred_at' => now(),
        ];
    }
}
