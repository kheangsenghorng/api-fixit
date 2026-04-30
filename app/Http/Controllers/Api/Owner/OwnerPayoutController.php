<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Models\OwnerPayout;
use Illuminate\Http\Request;


class OwnerPayoutController extends Controller
{
    public function index(Request $request)
    {
        $ownerId = auth()->id();

        $query = OwnerPayout::with(['split.payment'])
            ->where('owner_id', $ownerId);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payouts = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Your payouts fetched successfully',
            'data' => $payouts,
        ]);
    }

    public function stats()
    {
        $ownerId = auth()->id();

        $query = OwnerPayout::where('owner_id', $ownerId);

        return response()->json([
            'success' => true,
            'message' => 'Your payout stats fetched successfully',
            'data' => [
                'total_payouts' => $query->count(),
                'total_amount' => round((float) $query->sum('amount'), 2),

                'pending_count' => (clone $query)->where('status', 'pending')->count(),
                'paid_count' => (clone $query)->where('status', 'paid')->count(),
                'failed_count' => (clone $query)->where('status', 'failed')->count(),

                'pending_amount' => round((float) (clone $query)->where('status', 'pending')->sum('amount'), 2),
                'paid_amount' => round((float) (clone $query)->where('status', 'paid')->sum('amount'), 2),
                'failed_amount' => round((float) (clone $query)->where('status', 'failed')->sum('amount'), 2),
            ],
        ]);
    }

    public function show($id)
    {
        $ownerId = auth()->id();

        $payout = OwnerPayout::with(['split.payment'])
            ->where('owner_id', $ownerId)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Your payout fetched successfully',
            'data' => $payout,
        ]);
    }
}