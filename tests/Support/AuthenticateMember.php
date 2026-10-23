<?php

namespace Tests\Support;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateMember
{
    public function handle(Request $request, Closure $next): Response
    {
        $member = Member::find($request->header('X-Member'));
        if (! $member) {
            return response('Unauthenticated', 401);
        }
        $request->setUserResolver(fn () => $member);

        return $next($request);
    }
}
