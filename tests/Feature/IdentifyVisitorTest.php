<?php

namespace Tests\Feature;

use Tests\Support\AuthenticateMember;
use Tests\Support\Member;
use Tests\TestCase;
use Whilesmart\Engagement\Models\EngagementEvent;

class IdentifyVisitorTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('engagement.identify_route_middleware', ['api', AuthenticateMember::class]);
    }

    public function test_a_signed_in_visitor_takes_their_earlier_and_later_visits(): void
    {
        $member = Member::create(['name' => 'Ada']);
        $this->visit('visitor-1', 'session-1', 'newsletter');

        $this->postJson('/api/engagement/identify', ['visitor_id' => 'visitor-1'], ['X-Member' => $member->id])
            ->assertOk()->assertJsonPath('data.linked', 1);
        $this->visit('visitor-1', 'session-2');

        $this->assertSame(2, EngagementEvent::query()->where('name', 'page.view')->where('subject_type', $member->getMorphClass())->where('subject_id', $member->id)->count());
        $visitors = collect($this->getJson('/api/engagement/report?days=1')->assertOk()->json('data.groups'))->firstWhere('key', 'visitors');
        $metrics = collect($visitors['metrics'])->keyBy('key');
        $this->assertSame(1, $metrics['identified_visitors']['value']);
        $this->assertSame([['label' => 'newsletter', 'value' => 1]], $metrics['identified_by_source']['rows']);
    }

    public function test_a_shared_browser_keeps_what_the_first_account_did(): void
    {
        $first = Member::create(['name' => 'Ada']);
        $second = Member::create(['name' => 'Grace']);
        $this->visit('visitor-1', 'session-1');
        $this->postJson('/api/engagement/identify', ['visitor_id' => 'visitor-1'], ['X-Member' => $first->id])->assertOk();

        $this->postJson('/api/engagement/identify', ['visitor_id' => 'visitor-1'], ['X-Member' => $second->id])
            ->assertOk()->assertJsonPath('data.linked', 0);
        $this->visit('visitor-1', 'session-2');

        $this->assertSame([$first->id, $second->id], EngagementEvent::query()->where('name', 'page.view')->orderBy('id')->pluck('subject_id')->all());
        $this->assertSame('website', EngagementEvent::query()->where('name', 'visitor.identified')->value('client_key'));
    }

    public function test_signing_in_again_on_every_page_adds_nothing(): void
    {
        $member = Member::create(['name' => 'Ada']);
        $this->visit('visitor-1', 'session-1');
        foreach (range(1, 3) as $load) {
            $this->postJson('/api/engagement/identify', ['visitor_id' => 'visitor-1'], ['X-Member' => $member->id])->assertOk();
        }
        $this->postJson('/api/engagement/identify', ['visitor_id' => 'visitor-2'], ['X-Member' => $member->id])->assertOk();

        $this->assertSame(['page.view', 'visitor.identified'], EngagementEvent::query()->orderBy('id')->pluck('name')->all());
    }

    public function test_a_visitor_is_credited_to_where_they_first_came_from_before_the_period(): void
    {
        $member = Member::create(['name' => 'Ada']);
        $this->visit('visitor-1', 'session-1', 'newsletter', now()->subDays(40)->toIso8601String());
        $this->visit('visitor-1', 'session-2', 'direct');
        $this->postJson('/api/engagement/identify', ['visitor_id' => 'visitor-1'], ['X-Member' => $member->id])->assertOk();

        $visitors = collect($this->getJson('/api/engagement/report?days=30&client=website')->assertOk()->json('data.groups'))->firstWhere('key', 'visitors');
        $metrics = collect($visitors['metrics'])->keyBy('key');
        $this->assertSame(1, $metrics['identified_visitors']['value']);
        $this->assertSame([['label' => 'newsletter', 'value' => 1]], $metrics['identified_by_source']['rows']);
    }

    public function test_linking_needs_a_signed_in_subject_and_a_visitor(): void
    {
        $this->postJson('/api/engagement/identify', ['visitor_id' => 'visitor-1'])->assertUnauthorized();
        $member = Member::create(['name' => 'Ada']);
        $this->postJson('/api/engagement/identify', [], ['X-Member' => $member->id])->assertUnprocessable();
    }

    private function visit(string $visitor, string $session, ?string $source = null, ?string $at = null): void
    {
        $this->withHeaders(['Origin' => 'https://www.example.com', 'X-Engagement-Site-Key' => 'website-key'])
            ->postJson('/api/engagement/events', ['events' => [array_filter([
                'name' => 'page.view', 'visitor_id' => $visitor, 'session_id' => $session, 'source' => $source,
                'occurred_at' => $at ?? now()->subSecond()->toIso8601String(),
            ])]])->assertAccepted();
    }
}
