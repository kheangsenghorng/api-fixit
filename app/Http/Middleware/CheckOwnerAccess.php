<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Owner;
use Illuminate\Support\Facades\Auth;

class CheckOwnerAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Admin must provide owner_id
        if ($user->role === 'admin') {

            if (!$request->owner_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'owner_id is required for admin'
                ], 422);
            }

            return $next($request);
        }

        // Owner user
        $owner = Owner::where('user_id', $user->id)->first();

        if (!$owner) {
            return response()->json([
                'success' => false,
                'message' => 'Owner account not found'
            ], 403);
        }

        // Force owner_id
        $request->merge([
            'owner_id' => $owner->id
        ]);

        return $next($request);
    }
}