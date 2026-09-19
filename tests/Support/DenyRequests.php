<?php

namespace Tests\Support;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DenyRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        return response('Protected', 401);
    }
}
