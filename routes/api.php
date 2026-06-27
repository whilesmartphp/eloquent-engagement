<?php

use Illuminate\Support\Facades\Route;
use Whilesmart\Engagement\Http\Controllers\EngagementReportController;

Route::get('engagement/report', [EngagementReportController::class, 'show']);
