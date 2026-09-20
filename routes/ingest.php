<?php

use Illuminate\Support\Facades\Route;
use Whilesmart\Engagement\Http\Controllers\StoreBrowserEventsController;

Route::post('engagement/events', StoreBrowserEventsController::class);
