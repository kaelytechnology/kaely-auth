<?php

namespace Kaely\Auth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Check if user has any of the required roles
            $hasRole = false;
            foreach ($roles as $role) {
                if ($user->hasRole($role)) {
                    $hasRole = true;
                    break;
                }
            }

            if (!$hasRole) {
                Log::warning('KaelyAuth: Role denied', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'required_roles' => $roles,
                    'user_roles' => $user->roles->pluck('slug'),
                    'route' => $request->route()->getName(),
                    'method' => $request->method(),
                    'url' => $request->fullUrl()
                ]);

                return response()->json([
                    'error' => 'Forbidden',
                    'message' => 'Insufficient role privileges',
                    'required_roles' => $roles
                ], 403);
            }

            return $next($request);
        } catch (\Exception $e) {
            Log::error('KaelyAuth: Error in role middleware', [
                'error' => $e->getMessage(),
                'roles' => $roles,
                'route' => $request->route()->getName()
            ]);

            return response()->json([
                'error' => 'Internal Server Error',
                'message' => 'Error checking roles'
            ], 500);
        }
    }
} 