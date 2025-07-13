<?php

namespace Kaely\Auth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Kaely\Auth\Exceptions\KaelyAuthException;

class CheckPermission
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$permissions)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Check if user has any of the required permissions
            $hasPermission = false;
            foreach ($permissions as $permission) {
                if ($user->hasPermission($permission)) {
                    $hasPermission = true;
                    break;
                }
            }

            if (!$hasPermission) {
                Log::warning('KaelyAuth: Permission denied', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'required_permissions' => $permissions,
                    'user_permissions' => $user->roles->flatMap->permissions->pluck('slug'),
                    'route' => $request->route()->getName(),
                    'method' => $request->method(),
                    'url' => $request->fullUrl()
                ]);

                return response()->json([
                    'error' => 'Forbidden',
                    'message' => 'Insufficient permissions',
                    'required_permissions' => $permissions
                ], 403);
            }

            return $next($request);
        } catch (\Exception $e) {
            Log::error('KaelyAuth: Error in permission middleware', [
                'error' => $e->getMessage(),
                'permissions' => $permissions,
                'route' => $request->route()->getName()
            ]);

            return response()->json([
                'error' => 'Internal Server Error',
                'message' => 'Error checking permissions'
            ], 500);
        }
    }
} 