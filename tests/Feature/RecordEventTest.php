<?php

namespace Tests\Feature;

use Tests\Support\Member;
use Tests\TestCase;
use Whilesmart\Engagement\Facades\Engagement;
use Whilesmart\Engagement\Models\EngagementEvent;

class RecordEventTest extends TestCase
{
    public function test_it_records_an_event_attributed_to_a_subject(): void
    {
        $member = Member::create(['name' => 'Ada']);

        $event = Engagement::record($member, 'transaction.created', ['amount' => 10]);

        $this->assertDatabaseHas('engagement_events', [
            'id' => $event->id,
            'name' => 'transaction.created',
            'subject_type' => Member::class,
            'subject_id' => $member->id,
        ]);
        $this->assertSame(['amount' => 10], $event->fresh()->metadata);
        $this->assertTrue($member->engagementEvents()->whereKey($event->id)->exists());
    }

    public function test_it_records_an_anonymous_event(): void
    {
        $event = Engagement::record(null, 'session.start');

        $this->assertNull($event->subject_id);
        $this->assertNull($event->subject_type);
        $this->assertSame('session.start', $event->name);
    }

    public function test_the_subject_trait_records_its_own_event(): void
    {
        $member = Member::create(['name' => 'Grace']);

        $member->recordEngagement('report.viewed');

        $this->assertSame(1, EngagementEvent::where('name', 'report.viewed')->count());
    }
}
