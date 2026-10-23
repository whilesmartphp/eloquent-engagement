<?php

namespace Whilesmart\Engagement\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Whilesmart\Engagement\Models\EngagementEvent;

class IdentifyVisitorController extends Controller
{
    /**
     * Attributes a browser visitor's events to the signed-in subject.
     *
     * Only events nobody holds yet are taken, so a shared browser keeps what an
     * earlier account did. Events the visitor sends later follow the newest link.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $subject = $request->user();
        abort_unless($subject instanceof Model, 401);
        $visitorId = $request->validate(['visitor_id' => ['required', 'string', 'max:100']])['visitor_id'];

        $linked = EngagementEvent::query()
            ->where('visitor_id', $visitorId)
            ->whereNull('subject_id')
            ->update(['subject_type' => $subject->getMorphClass(), 'subject_id' => $subject->getKey(), 'updated_at' => now()]);
        // Read after the update: unheld events only exist before a visitor's first link, so the
        // newest held event now says whether this subject already holds the visitor.
        $current = EngagementEvent::query()->where('visitor_id', $visitorId)->whereNotNull('subject_id')->latest('id')->first(['subject_type', 'subject_id']);
        $alreadyLinked = $current && $current->subject_type === $subject->getMorphClass() && (string) $current->subject_id === (string) $subject->getKey();
        $alreadyLinked || EngagementEvent::query()->create([
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'name' => 'visitor.identified',
            'visitor_id' => $visitorId,
            'occurred_at' => now(),
        ]);

        return response()->json(['success' => true, 'data' => ['visitor_id' => $visitorId, 'linked' => $linked]]);
    }
}
