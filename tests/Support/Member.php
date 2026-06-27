<?php

namespace Tests\Support;

use Illuminate\Database\Eloquent\Model;
use Whilesmart\Engagement\Traits\RecordsEngagement;

class Member extends Model
{
    use RecordsEngagement;

    protected $guarded = [];
}
