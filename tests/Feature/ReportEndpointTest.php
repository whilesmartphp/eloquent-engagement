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
                    'groups' => [['key', 'label', 'metrics']],
                ],
            ]);

        $keys = collect($response->json('data.groups'))->pluck('key');
        $this->assertTrue($keys->contains('activity'));
    }
}
