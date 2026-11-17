<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        $roles = Role::with('permissions')->get();
        $allPermissions = Permission::all()->groupBy('module');

        return response()->json([
            'success' => true,
            'data' => $roles,
            'available_permissions' => $allPermissions,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:roles,slug',
            'description' => 'nullable|string',
            'permission_ids' => 'nullable|array',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $role = Role::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
        ]);

        if (!empty($validated['permission_ids'])) {
            $role->permissions()->sync($validated['permission_ids']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully',
            'data' => $role->load('permissions'),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $role = Role::find($id);
        if (!$role) {
            return response()->json(['success' => false, 'message' => 'Role not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permission_ids' => 'nullable|array',
        ]);

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        if (array_key_exists('permission_ids', $validated)) {
            $role->permissions()->sync($validated['permission_ids'] ?? []);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully',
            'data' => $role->load('permissions'),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $role = Role::find($id);
        if (!$role) {
            return response()->json(['success' => false, 'message' => 'Role not found'], 404);
        }

        if ($role->slug === 'admin') {
            return response()->json(['success' => false, 'message' => 'Super admin role cannot be deleted.'], 403);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully',
        ]);
    }
}
