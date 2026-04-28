<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSystemRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ($roles !== [] && ! in_array($user->system_role, $roles, true))) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
