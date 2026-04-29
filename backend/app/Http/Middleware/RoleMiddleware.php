<?php

namespace App\Http\Middleware;

class RoleMiddleware
{
    public function handle($request, $next)
    {
        // TODO: check user role / permissions

        return $next($request);
    }
}