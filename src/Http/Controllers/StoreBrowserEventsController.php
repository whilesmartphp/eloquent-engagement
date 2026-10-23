<?php

namespace Whilesmart\Engagement\Http\Controllers;

use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Whilesmart\Engagement\Http\Requests\StoreBrowserEventsRequest;
use Whilesmart\Engagement\Models\EngagementEvent;

class StoreBrowserEventsController extends Controller
{
    public function __invoke(StoreBrowserEventsRequest $request): JsonResponse
    {
        $batch = collect($request->validated('events'));
        $subjects = $this->linkedSubjects($batch->pluck('visitor_id')->unique()->values()->all());
        $events = $batch->map(fn (array $event) => [
            'subject_type' => $subjects[$event['visitor_id']]->subject_type ?? null,
            'subject_id' => $subjects[$event['visitor_id']]->subject_id ?? null,
            'name' => $event['name'],
            'client_key' => $request->clientKey(),
            'visitor_id' => $event['visitor_id'],
            'session_id' => $event['session_id'],
            'source' => $event['source'] ?? null,
            'utm_medium' => $event['utm_medium'] ?? null,
            'utm_campaign' => $event['utm_campaign'] ?? null,
            'utm_term' => $event['utm_term'] ?? null,
            'utm_content' => $event['utm_content'] ?? null,
            'click_id' => $event['click_id'] ?? null,
            'page_url' => $event['url'] ?? null,
            'referrer' => $event['referrer'] ?? null,
            'metadata' => isset($event['properties']) ? json_encode($event['properties'], JSON_THROW_ON_ERROR) : null,
            'occurred_at' => isset($event['occurred_at']) ? CarbonImmutable::parse($event['occurred_at']) : now(),
            'created_at' => now(),
            'updated_at' => now(),
        ])->all();

        EngagementEvent::query()->insert($events);

        return response()->json(['success' => true, 'data' => ['accepted' => count($events)]], 202);
    }

    /**
     * The subject each visitor was most recently linked to.
     *
     * @param  array<int, string>  $visitorIds
     * @return array<string, object>
     */
    private function linkedSubjects(array $visitorIds): array
    {
        $latest = EngagementEvent::query()
            ->whereIn('visitor_id', $visitorIds)
            ->whereNotNull('subject_id')
            ->groupBy('visitor_id')
            ->selectRaw('visitor_id, max(id) as id');

        return EngagementEvent::query()
            ->joinSub($latest, 'latest', fn ($join) => $join->on((new EngagementEvent)->getTable().'.id', '=', 'latest.id'))
            ->get(['latest.visitor_id', 'subject_type', 'subject_id'])
            ->keyBy('visitor_id')
            ->all();
    }
}
