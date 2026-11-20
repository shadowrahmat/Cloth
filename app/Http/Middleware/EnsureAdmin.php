<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts the admin API to active administrators and managers.
 *
 * `auth:sanctum` alone accepts any valid token, including customer tokens
 * issued by /api/v1/customer/register, so every admin route must also pass
 * through this check. The rule mirrors Admin\AuthController::login().
 *
 * `admin:full` additionally shuts out managers. Use it on anything that could
 * grant privileges or expose credentials (users, roles, SMTP settings), so a
 * manager cannot promote themselves to admin.
 */
class EnsureAdmin
{
    public function handle(Request $request, Closure $next, ?string $level = null): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active || (! $user->isAdmin() && ! $user->hasRole('manager'))) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Administrator privileges required.',
            ], 403);
        }

        if ($level === 'full' && ! $user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only administrators can do this.',
            ], 403);
        }

        return $next($request);
    }
}
