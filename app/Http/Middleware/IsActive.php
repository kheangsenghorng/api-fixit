<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsActive
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || !$request->user()->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Account not verified. Please complete OTP verification.'
            ], 403);
        }

        return $next($request);
    }
}
