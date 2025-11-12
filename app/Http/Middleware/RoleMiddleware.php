<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RoleMiddleware
{
    /**
     * Usage: ->middleware('role:compte:create') or 'role:compte' (will look for compte:* scopes)
     */
    public function handle(Request $request, Closure $next, string $ability = null)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['error' => 'unauthenticated'], 401);
        }

        // If no specific ability provided, allow (or you can choose to deny)
        if (! $ability) {
            return $next($request);
        }

        // Check token scopes (Passport)
        try {
            $token = $user->token();
            if ($token) {
                // exact scope match
                if ($token->can($ability) || $token->can($ability . ':*')) {
                    return $next($request);
                }

                // support ability like 'compte' meaning any compte scope
                [$resource, $action] = array_pad(explode(':', $ability), 2, null);
                if ($action === null) {
                    // look for scopes starting with resource
                    foreach ($token->scopes as $scope) {
                        if (str_starts_with($scope, $resource)) {
                            return $next($request);
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('RoleMiddleware check failed: ' . $e->getMessage());
        }

        return response()->json(['error' => 'forbidden'], 403);
    }
}
