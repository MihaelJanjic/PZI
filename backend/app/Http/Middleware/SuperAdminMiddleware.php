<?php

namespace App\Http\Middleware;

use Closure;

class SuperAdminMiddleware
{
    public function handle($request, Closure $next)
    {
        if (!$request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
