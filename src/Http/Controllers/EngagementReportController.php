<?php

namespace Whilesmart\Engagement\Http\Controllers;

use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Whilesmart\Engagement\EngagementManager;
use Whilesmart\Engagement\Support\Period;

class EngagementReportController extends Controller
{
    public function show(Request $request, EngagementManager $engagement): JsonResponse
    {
        $granularity = in_array($request->query('granularity'), ['day', 'week', 'month'], true)
            ? $request->query('granularity')
            : 'day';

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $period = new Period(
                CarbonImmutable::parse($request->query('start_date')),
                CarbonImmutable::parse($request->query('end_date')),
                $granularity
            );
        } else {
            $period = Period::lastDays((int) $request->query('days', 30), $granularity);
        }

        return response()->json([
            'success' => true,
            'data' => $engagement->report($period),
        ]);
    }
}
