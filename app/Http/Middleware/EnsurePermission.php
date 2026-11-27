<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforces the role permissions (e.g. `view_orders`) on the admin API.
 *
 * Usage on a route or group:
 *   permission:orders          the module name; the action comes from the HTTP method
 *                              (GET = view, POST = create, PUT/PATCH = update, DELETE = delete)
 *   permission:update_orders   one exact permission, for routes where the method is not enough
 *
 * Administrators always pass. Must run after the `admin` middleware.
 */
class EnsurePermission
{
    private const ACTIONS = ['view', 'create', 'update', 'delete'];

    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $slug = self::isExactSlug($permission) ? $permission : self::slugFor($permission, $request->method());
        $user = $request->user();

        if (! $user || ! ($user->isAdmin() || $user->hasPermission($slug))) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. You do not have permission to do this.',
            ], 403);
        }

        return $next($request);
    }

    /** The permission slug a given HTTP method needs on a module, e.g. orders + PUT = update_orders. */
    public static function slugFor(string $module, string $method): string
    {
        $action = match (strtoupper($method)) {
            'POST' => 'create',
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'delete',
            default => 'view',
        };

        return "{$action}_{$module}";
    }

    private static function isExactSlug(string $permission): bool
    {
        foreach (self::ACTIONS as $action) {
            if (str_starts_with($permission, $action.'_')) {
                return true;
            }
        }

        return false;
    }
}
