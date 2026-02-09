<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Enforce role-based access using pipe-delimited roles (e.g., teacher|director).
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        $user = $request->user();
        $allowedRoles = array_map('trim', explode('|', $role));

        if (!$user || !in_array($user->role, $allowedRoles, true)) {
            abort(403, 'Unauthorized role.');
        }

        return $next($request);
    }
}
