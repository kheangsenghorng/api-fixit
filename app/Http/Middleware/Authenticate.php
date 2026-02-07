<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    protected function redirectTo($request)
    {
        // For API: never redirect, just return null
        if (! $request->expectsJson()) {
            return route('login');
        }
    }
}
