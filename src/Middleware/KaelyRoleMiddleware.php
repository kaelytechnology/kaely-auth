<?php

namespace Kaely\Auth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Kaely\Auth\Services\PermissionService;

class KaelyRoleMiddleware
{
    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Authentication required'
            ], 401);
        }

        $user = Auth::user();

        // If no specific roles required, just check if user is authenticated
        if (empty($roles)) {
            return $next($request);
        }

        // Check if user has any of the required roles
        $hasRole = false;
        $requiredRoles = [];

        foreach ($roles as $role) {
            $requiredRoles[] = $role;
            
            if ($this->permissionService->userHasRole($user, $role)) {
                $hasRole = true;
                break;
            }
        }

        if (!$hasRole) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'Insufficient role privileges',
                'required_roles' => $requiredRoles,
                'user_roles' => $this->permissionService->getUserRoles($user)
            ], 403);
        }

        return $next($request);
    }
} 