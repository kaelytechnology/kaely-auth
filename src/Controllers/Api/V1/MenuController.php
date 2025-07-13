<?php

namespace Kaely\Auth\Controllers\Api\V1;

use Kaely\Auth\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Kaely\Auth\Models\Menu;
use Kaely\Auth\Models\Role;

class MenuController extends Controller
{
    /**
     * Get all menu items
     */
    public function index(Request $request): JsonResponse
    {
        $role = $request->input('role');
        $parentId = $request->input('parent_id', 0);

        $query = Menu::with(['children', 'roles']);

        if ($role) {
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }

        if ($parentId === 0) {
            $query->whereNull('parent_id');
        } else {
            $query->where('parent_id', $parentId);
        }

        $menus = $query->orderBy('order')->get();

        return response()->json([
            'success' => true,
            'data' => $menus
        ]);
    }

    /**
     * Get menu for specific user
     */
    public function userMenu(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $userRoles = $user->roles->pluck('name')->toArray();

        $menus = Menu::with(['children', 'roles'])
            ->whereHas('roles', function ($query) use ($userRoles) {
                $query->whereIn('name', $userRoles);
            })
            ->whereNull('parent_id')
            ->orderBy('order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $menus
        ]);
    }

    /**
     * Get all menu items (admin)
     */
    public function all(Request $request): JsonResponse
    {
        $menus = Menu::with(['children', 'roles'])
            ->whereNull('parent_id')
            ->orderBy('order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $menus
        ]);
    }

    /**
     * Get a specific menu item
     */
    public function show(int $id): JsonResponse
    {
        $menu = Menu::with(['children', 'roles'])->find($id);

        if (!$menu) {
            return response()->json([
                'success' => false,
                'message' => 'Menu item not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $menu
        ]);
    }

    /**
     * Create a new menu item
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'display_name' => 'required|string|max:255',
            'url' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:menus,id',
            'order' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $menu = Menu::create([
            'name' => $request->input('name'),
            'display_name' => $request->input('display_name'),
            'url' => $request->input('url'),
            'icon' => $request->input('icon'),
            'parent_id' => $request->input('parent_id'),
            'order' => $request->input('order'),
            'is_active' => $request->input('is_active', true),
        ]);

        if ($request->has('roles')) {
            $menu->roles()->attach($request->input('roles'));
        }

        $menu->load(['children', 'roles']);

        return response()->json([
            'success' => true,
            'message' => 'Menu item created successfully',
            'data' => $menu
        ], 201);
    }

    /**
     * Update a menu item
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $menu = Menu::find($id);

        if (!$menu) {
            return response()->json([
                'success' => false,
                'message' => 'Menu item not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'display_name' => 'sometimes|required|string|max:255',
            'url' => 'sometimes|required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:menus,id',
            'order' => 'sometimes|required|integer|min:0',
            'is_active' => 'boolean',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $menu->update($request->only([
            'name', 'display_name', 'url', 'icon', 'parent_id', 'order', 'is_active'
        ]));

        if ($request->has('roles')) {
            $menu->roles()->sync($request->input('roles'));
        }

        $menu->load(['children', 'roles']);

        return response()->json([
            'success' => true,
            'message' => 'Menu item updated successfully',
            'data' => $menu
        ]);
    }

    /**
     * Delete a menu item
     */
    public function destroy(int $id): JsonResponse
    {
        $menu = Menu::find($id);

        if (!$menu) {
            return response()->json([
                'success' => false,
                'message' => 'Menu item not found'
            ], 404);
        }

        // Check if menu has children
        if ($menu->children()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete menu item that has children'
            ], 422);
        }

        $menu->delete();

        return response()->json([
            'success' => true,
            'message' => 'Menu item deleted successfully'
        ]);
    }

    /**
     * Reorder menu items
     */
    public function reorder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.id' => 'required|exists:menus,id',
            'items.*.order' => 'required|integer|min:0',
            'items.*.parent_id' => 'nullable|exists:menus,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        foreach ($request->input('items') as $item) {
            Menu::where('id', $item['id'])->update([
                'order' => $item['order'],
                'parent_id' => $item['parent_id'] ?? null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Menu items reordered successfully'
        ]);
    }

    /**
     * Get menu item's roles
     */
    public function roles(int $id): JsonResponse
    {
        $menu = Menu::with('roles')->find($id);

        if (!$menu) {
            return response()->json([
                'success' => false,
                'message' => 'Menu item not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $menu->roles
        ]);
    }

    /**
     * Assign roles to menu item
     */
    public function assignRoles(Request $request, int $id): JsonResponse
    {
        $menu = Menu::find($id);

        if (!$menu) {
            return response()->json([
                'success' => false,
                'message' => 'Menu item not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $menu->roles()->sync($request->input('roles'));

        return response()->json([
            'success' => true,
            'message' => 'Roles assigned successfully'
        ]);
    }
} 