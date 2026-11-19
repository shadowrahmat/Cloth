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
 */
class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active || (! $user->isAdmin() && ! $user->hasRole('manager'))) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Administrator privileges required.',
            ], 403);
        }

        return $next($request);
    }
}
