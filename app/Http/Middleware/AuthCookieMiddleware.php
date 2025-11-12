<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthCookieMiddleware
{
    /**
     * If an access_token cookie exists, set the Authorization header so Laravel's guard can pick it up.
     */
    public function handle(Request $request, Closure $next)
    {
        $access = $request->cookie('access_token');
        if ($access && ! $request->header('Authorization')) {
            $request->headers->set('Authorization', 'Bearer ' . $access);
        }

        return $next($request);
    }
}
