<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Owner;
use App\Models\OwnerDocument;

class CheckOwnerDocument
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            return $next($request);
        }

        $owner = Owner::where('user_id', $user->id)->first();

        if (!$owner) {
            return response()->json([
                'success' => false,
                'message' => 'Owner account not found'
            ], 403);
        }

        $document = OwnerDocument::where('owner_id', $owner->id)->first();

        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'You must upload verification document before creating services'
            ], 403);
        }

        $request->merge([
            'owner_id' => $owner->id
        ]);

        return $next($request);
    }
}