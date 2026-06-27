<?php

namespace Tests\Feature;

use Tests\Support\Member;
use Tests\Support\SignupsMetricProvider;
use Tests\TestCase;
use Whilesmart\Engagement\EngagementManager;
use Whilesmart\Engagement\Facades\Engagement;
use Whilesmart\Engagement\Support\Period;

class ReportTest extends TestCase
{
    public function test_the_report_includes_built_in_activity_metrics(): void
    {
        $a = Member::create(['name' => 'A']);
        $b = Member::create(['name' => 'B']);
        Engagement::record($a, 'session.start');
        Engagement::record($a, 'session.start');
        Engagement::record($b, 'session.start');

        $report = app(EngagementManager::class)->report(Period::lastDays(7));

        $activity = collect($report['groups'])->firstWhere('key', 'activity');
        $this->assertNotNull($activity);

        $metrics = collect($activity['metrics'])->keyBy('key');
        $this->assertSame(3, $metrics['total_events']['value']);
        $this->assertSame(2, $metrics['active_subjects']['value']);
        $this->assertNotEmpty($metrics['active_subjects_series']['series']);

        $features = $metrics['top_features'];
        $this->assertSame('ranking', $features['type']);
        $this->assertSame('session.start', $features['rows'][0]['label']);
        $this->assertSame(3, $features['rows'][0]['value']);
    }

    public function test_a_host_provider_drives_its_own_group(): void
    {
        Member::create(['name' => 'A']);
        Member::create(['name' => 'B']);

        Engagement::registerMetricProvider(SignupsMetricProvider::class);

        $report = app(EngagementManager::class)->report(Period::lastDays(30));

        $members = collect($report['groups'])->firstWhere('key', 'members');
        $this->assertNotNull($members);
        $this->assertSame('Members', $members['label']);

        $total = collect($members['metrics'])->firstWhere('key', 'total_members');
        $this->assertSame(2, $total['value']);
    }
}
