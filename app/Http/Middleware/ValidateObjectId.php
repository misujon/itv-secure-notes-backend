<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateObjectId
{
    public function handle(Request $request, Closure $next, string $parameter = 'id'): Response
    {
        $value = $request->route($parameter);

        if (! is_string($value) || preg_match('/^[a-f\d]{24}$/i', $value) !== 1) {
            return ApiResponse::error('Invalid resource identifier format.', 422);
        }

        return $next($request);
    }
}
