<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error('Unauthenticated.', 401);
            }

            return redirect()->guest(route('admin.login'));
        }

        if (! in_array($user->role, $roles, true)) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error('Forbidden. Insufficient permissions.', 403);
            }

            abort(403, 'You do not have permission to access the administration portal.');
        }

        return $next($request);
    }
}
