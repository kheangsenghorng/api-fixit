<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\OwnerPayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\OwnerPayoutPaidMail;
use App\Models\Owner;

class OwnerPayoutController extends Controller
{
    // This method provides a paginated list of 
    //owner payouts with optional filters for owner, 
    //status, and date range.
    public function index(Request $request)
    {
        $query = OwnerPayout::with(['owner', 'split.payment']);

        if ($request->filled('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $payouts = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Owner payouts fetched successfully',
            'data' => $payouts,
        ]);
    }

    // This method provides aggregated payout statistics with 
    //optional filters for owner and date range.
    public function stats(Request $request)
    {
        $query = OwnerPayout::query();

        if ($request->filled('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        return response()->json([
            'success' => true,
            'message' => 'Owner payout stats fetched successfully',
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
    
    // This method provides aggregated payout data grouped by owner, with optional 
    //date filters and a monthly filter.
    public function amountByOwner(Request $request)
    {
        $query = OwnerPayout::query()
            ->join('owners', 'owner_payouts.owner_id', '=', 'owners.id')
            ->join('users', 'owners.user_id', '=', 'users.id');
    
        // Optional filters
        if ($request->filled('from_date')) {
            $query->whereDate('owner_payouts.created_at', '>=', $request->from_date);
        }
    
        if ($request->filled('to_date')) {
            $query->whereDate('owner_payouts.created_at', '<=', $request->to_date);
        }
    
        // Optional monthly filter
        if ($request->boolean('monthly')) {
            $query->whereMonth('owner_payouts.created_at', now()->month)
                  ->whereYear('owner_payouts.created_at', now()->year);
        }
    
        $data = $query
            ->select([
                'owner_payouts.owner_id',
                'owners.business_name',
                'owners.user_id',
                'users.name as owner_name',
            ])
            ->selectRaw('COUNT(owner_payouts.id) as total_payouts')
            ->selectRaw("SUM(owner_payouts.status = 'pending') as pending_count")
            ->selectRaw("SUM(owner_payouts.status = 'paid') as paid_count")
            ->selectRaw("SUM(owner_payouts.status = 'failed') as failed_count")
            ->selectRaw("COALESCE(SUM(CASE WHEN owner_payouts.status = 'pending' THEN owner_payouts.amount ELSE 0 END), 0) as pending_amount")
            ->selectRaw("COALESCE(SUM(CASE WHEN owner_payouts.status = 'paid' THEN owner_payouts.amount ELSE 0 END), 0) as paid_amount")
            ->selectRaw("COALESCE(SUM(CASE WHEN owner_payouts.status = 'failed' THEN owner_payouts.amount ELSE 0 END), 0) as failed_amount")
            ->selectRaw('COALESCE(SUM(owner_payouts.amount), 0) as total_amount')
            ->groupBy([
                'owner_payouts.owner_id',
                'owners.business_name',
                'owners.user_id',
                'users.name',
            ])
            ->orderByDesc('total_amount')
            ->get();
    
        return response()->json([
            'success' => true,
            'message' => 'Owner payout amounts fetched successfully',
            'data' => $data,
        ]);
    }



    // This method retrieves detailed information about a specific owner payout,
    /// including related owner and payment data.
    public function show($id)
    {
        $payout = OwnerPayout::with(['owner', 'split.payment'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Owner payout fetched successfully',
            'data' => $payout,
        ]);
    }
    // This method allows updating the status of an owner payout,
    // along with an optional transaction reference.
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,paid,failed',
            'transaction_reference' => 'nullable|string|max:255',
        ]);

        $payout = OwnerPayout::findOrFail($id);

        $payout->update([
            'status' => $request->status,
            'transaction_reference' => $request->transaction_reference,
            'paid_at' => $request->status === 'paid' ? now() : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Owner payout status updated successfully',
            'data' => $payout,
        ]);
    }

    // This method processes multiple owner payouts as paid, updates 
    //their status and transaction reference, and sends an email notification to the owner.
    public function payMultipleAndSendEmail(Request $request)
{
    $validated = $request->validate([
        'owner_id' => ['required', 'exists:owners,id'],
        'payout_ids' => ['required', 'array', 'min:1'],
        'payout_ids.*' => ['integer', 'distinct'],
        'method' => ['required', 'in:bank_transfer,card,cash,bakong,khqr'],
        'transaction_reference' => ['required', 'string', 'max:255'],
    ]);

    $owner = Owner::select('id', 'user_id')
        ->with('user:id,email')
        ->findOrFail($validated['owner_id']);

    $payoutIds = array_unique($validated['payout_ids']);

    // 🚀 FAST: Only count, no heavy loading
    $validCount = OwnerPayout::where('owner_id', $owner->id)
        ->whereIn('id', $payoutIds)
        ->where('status', 'pending')
        ->count();

    if ($validCount !== count($payoutIds)) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid payouts.',
        ], 422);
    }

    // 🚀 FAST: get total only (no collection)
    $totalAmount = OwnerPayout::whereIn('id', $payoutIds)->sum('amount');

    $paidAt = now();

    // 🚀 FASTEST: single query update
    OwnerPayout::where('owner_id', $owner->id)
        ->whereIn('id', $payoutIds)
        ->where('status', 'pending')
        ->update([
            'method' => $validated['method'],
            'status' => 'paid',
            'transaction_reference' => $validated['transaction_reference'],
            'paid_at' => $paidAt,
            'updated_at' => $paidAt,
        ]);

    // 🚀 OPTIONAL: Only load data IF you really need response items
    // (this is expensive — skip if not needed)
    $items = OwnerPayout::select('id', 'amount', 'method', 'status')
        ->whereIn('id', $payoutIds)
        ->get();

    // 🚀 QUEUE email (never send sync)
    if ($owner->user?->email) {
        dispatch(function () use ($owner, $payoutIds, $totalAmount, $validated) {
            Mail::to($owner->user->email)->send(
                new OwnerPayoutPaidMail(
                    $owner,
                    $payoutIds, // pass IDs instead of full models (lighter!)
                    $totalAmount,
                    $validated['transaction_reference']
                )
            );
        })->afterResponse(); // 🔥 does not block API
    }

    return response()->json([
        'success' => true,
        'message' => 'Paid successfully',
        'data' => [
            'owner_id' => $owner->id,
            'total_payouts' => count($payoutIds),
            'total_amount' => (float) $totalAmount,
            'items' => $items, // remove this if not needed → even faster
        ],
    ]);
}
}