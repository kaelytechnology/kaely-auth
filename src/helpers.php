<?php

if (!function_exists('kaely_auth')) {
    /**
     * Get the KaelyAuthManager instance.
     */
    function kaely_auth()
    {
        return app(\Kaely\Auth\KaelyAuthManager::class);
    }
}

if (!function_exists('kaely_has_permission')) {
    /**
     * Check if user has permission.
     */
    function kaely_has_permission($permission, $user = null)
    {
        return kaely_auth()->hasPermission($permission, $user);
    }
}

if (!function_exists('kaely_has_role')) {
    /**
     * Check if user has role.
     */
    function kaely_has_role($role, $user = null)
    {
        return kaely_auth()->hasRole($role, $user);
    }
}

if (!function_exists('kaely_get_user_permissions')) {
    /**
     * Get user permissions.
     */
    function kaely_get_user_permissions($user = null)
    {
        return kaely_auth()->getUserPermissions($user);
    }
}

if (!function_exists('kaely_get_user_roles')) {
    /**
     * Get user roles.
     */
    function kaely_get_user_roles($user = null)
    {
        return kaely_auth()->getUserRoles($user);
    }
}

if (!function_exists('kaely_get_user_menu')) {
    /**
     * Get user menu.
     */
    function kaely_get_user_menu($user = null)
    {
        return kaely_auth()->getUserMenu($user);
    }
} 