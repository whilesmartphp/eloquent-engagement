<?php

use Illuminate\Support\Facades\Route;
use Whilesmart\Engagement\Http\Controllers\IdentifyVisitorController;

Route::post('engagement/identify', IdentifyVisitorController::class);
