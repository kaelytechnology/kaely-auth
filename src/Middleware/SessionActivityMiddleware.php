<?php

namespace Kaely\Auth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Kaely\Auth\Services\SessionManagementService;

class SessionActivityMiddleware
{
    protected SessionManagementService $sessionManagementService;

    public function __construct(SessionManagementService $sessionManagementService)
    {
        $this->sessionManagementService = $sessionManagementService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if session management is enabled
        if (!config('kaely-auth.sessions.enabled', true)) {
            return $next($request);
        }

        // Check if activity tracking is enabled
        if (!config('kaely-auth.sessions.track_activity', true)) {
            return $next($request);
        }

        $response = $next($request);

        // Update session activity if user is authenticated
        if (Auth::check()) {
            $token = $request->bearerToken();
            if ($token) {
                $this->sessionManagementService->updateSessionActivity($token);
            }
        }

        return $response;
    }
} 