<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LoggingMiddleware
{
    /**
     * Log creation operations (POST) to the application log.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($request->isMethod('post')) {
            $payload = $request->except(['password', 'code']);
            Log::info('Creation operation', [
                'path' => $request->path(),
                'ip' => $request->ip(),
                'payload' => $payload,
            ]);
        }

        return $response;
    }
}
