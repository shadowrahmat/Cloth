<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::with('roles')->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        $perPage = (int) $request->input('per_page', 15);
        $users = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,manager,customer',
            'phone' => 'nullable|string|max:30',
            'is_active' => 'nullable|boolean',
            'role_ids' => 'nullable|array',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        if (!empty($validated['role_ids'])) {
            $user->roles()->sync($validated['role_ids']);
        }

        ActivityLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'create_user',
            'model_type' => User::class,
            'model_id' => $user->id,
            'description' => "Created user '{$user->name}' ({$user->role})",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user->load('roles'),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $user = User::with(['roles.permissions', 'addresses', 'orders' => fn($q) => $q->latest()->take(5)])->find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:admin,manager,customer',
            'phone' => 'nullable|string|max:30',
            'is_active' => 'nullable|boolean',
            'role_ids' => 'nullable|array',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->phone = $validated['phone'] ?? null;
        if (isset($validated['is_active'])) {
            $user->is_active = (bool) $validated['is_active'];
        }
        if (!empty($validated['password'])) {
            $user->password = $validated['password'];
        }
        $user->save();

        if (array_key_exists('role_ids', $validated)) {
            $user->roles()->sync($validated['role_ids'] ?? []);
        }

        ActivityLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'update_user',
            'model_type' => User::class,
            'model_id' => $user->id,
            'description' => "Updated user '{$user->name}'",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user->load('roles'),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        if ($user->id === $request->user()?->id) {
            return response()->json(['success' => false, 'message' => 'You cannot delete yourself.'], 403);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully',
        ]);
    }
}
