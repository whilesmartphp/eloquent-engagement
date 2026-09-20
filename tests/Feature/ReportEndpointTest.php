<?php

namespace Tests\Feature;

use Tests\Support\Member;
use Tests\TestCase;
use Whilesmart\Engagement\Facades\Engagement;

class ReportEndpointTest extends TestCase
{
    public function test_the_report_endpoint_returns_grouped_metrics(): void
    {
        $member = Member::create(['name' => 'A']);
        Engagement::record($member, 'session.start');

        $response = $this->getJson('api/engagement/report?days=7');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.period.granularity', 'day')
            ->assertJsonStructure([
                'data' => [
                    'period' => ['start', 'end', 'granularity'],
                    'groups' => [['key', 'label', 'client_scoped', 'metrics']],
                ],
            ]);

        $keys = collect($response->json('data.groups'))->pluck('key');
        $this->assertTrue($keys->contains('activity'));
    }

    public function test_the_report_says_which_groups_a_client_filter_narrows(): void
    {
        $response = $this->getJson('api/engagement/report?days=7');

        $groups = collect($response->json('data.groups'))->keyBy('key');

        $this->assertTrue($groups['visitors']['client_scoped']);
        $this->assertFalse($groups['activity']['client_scoped']);
    }

    public function test_the_report_rejects_a_client_it_does_not_know(): void
    {
        $this->getJson('api/engagement/report?client=typo')->assertStatus(422);
    }

    public function test_the_report_filters_browser_measurements_by_client(): void
    {
        foreach ([
            ['origin' => 'https://www.example.com', 'site_key' => 'website-key'],
            ['origin' => 'https://dashboard.example.com', 'site_key' => 'dashboard-key'],
        ] as $client) {
            $this->withHeaders([
                'Origin' => $client['origin'],
                'X-Engagement-Site-Key' => $client['site_key'],
            ])->postJson('/api/engagement/events', [
                'events' => [[
                    'name' => 'page.view',
                    'visitor_id' => $client['site_key'],
                    'session_id' => $client['site_key'],
                ]],
            ])->assertAccepted();
        }

        $response = $this->getJson('/api/engagement/report?client=website');
        $visitors = collect($response->json('data.groups'))->firstWhere('key', 'visitors');

        $response->assertOk()
            ->assertJsonPath('data.selected_client', 'website')
            ->assertJsonPath('data.clients.1.name', 'Website');
        $this->assertSame(1, collect($visitors['metrics'])->firstWhere('key', 'unique_visitors')['value']);
    }
}
