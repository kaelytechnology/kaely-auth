<?php

namespace Kaely\Auth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Kaely\Auth\Services\PermissionService;

class KaelyPermissionMiddleware
{
    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$permissions)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Authentication required'
            ], 401);
        }

        $user = Auth::user();

        // If no specific permissions required, just check if user is authenticated
        if (empty($permissions)) {
            return $next($request);
        }

        // Check if user has any of the required permissions
        $hasPermission = false;
        $requiredPermissions = [];

        foreach ($permissions as $permission) {
            $requiredPermissions[] = $permission;
            
            if ($this->permissionService->userHasPermission($user, $permission)) {
                $hasPermission = true;
                break;
            }
        }

        if (!$hasPermission) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'Insufficient permissions',
                'required_permissions' => $requiredPermissions,
                'user_permissions' => $this->permissionService->getUserPermissions($user)
            ], 403);
        }

        return $next($request);
    }
} 