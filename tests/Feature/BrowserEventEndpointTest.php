<?php

namespace Tests\Feature;

use Tests\Support\DenyRequests;
use Tests\TestCase;

class BrowserEventEndpointTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('engagement.route_middleware', ['api', DenyRequests::class]);
    }

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_browser_accepts_a_batch_from_an_allowed_site(): void
    {
        $this->withHeaders([
            'Origin' => 'https://www.example.com',
            'X-Engagement-Site-Key' => 'website-key',
        ])->postJson('/api/engagement/events', [
            'events' => [[
                'name' => 'page.view',
                'visitor_id' => 'visitor-1',
                'session_id' => 'session-1',
                'url' => 'https://example.com/pricing',
                'referrer' => 'https://search.example.test/result',
                'source' => 'newsletter',
                'utm_medium' => 'email',
                'utm_campaign' => 'launch',
                'click_id' => 'click-1',
                'properties' => ['campaign' => 'launch'],
            ]],
        ])->assertAccepted()->assertJsonPath('data.accepted', 1);

        $this->assertDatabaseHas('engagement_events', [
            'name' => 'page.view',
            'client_key' => 'website',
            'visitor_id' => 'visitor-1',
            'source' => 'newsletter',
            'utm_medium' => 'email',
            'utm_campaign' => 'launch',
            'click_id' => 'click-1',
        ]);
    }

    public function test_browser_rejects_an_unknown_origin_or_site_key(): void
    {
        $payload = ['events' => [[
            'name' => 'page.view',
            'visitor_id' => 'visitor-1',
            'session_id' => 'session-1',
        ]]];

        $this->withHeaders(['Origin' => 'https://unknown.example', 'X-Engagement-Site-Key' => 'website-key'])
            ->postJson('/api/engagement/events', $payload)->assertForbidden();
        $this->withHeaders(['Origin' => 'https://www.example.com', 'X-Engagement-Site-Key' => 'wrong'])
            ->postJson('/api/engagement/events', $payload)->assertForbidden();
    }

    public function test_browser_accepts_the_public_site_key_in_a_beacon_payload(): void
    {
        $this->withHeader('Origin', 'https://www.example.com')->postJson('/api/engagement/events', [
            'site_key' => 'website-key',
            'events' => [[
                'name' => 'page.view',
                'visitor_id' => 'visitor-2',
                'session_id' => 'session-2',
            ]],
        ])->assertAccepted();
    }
}
