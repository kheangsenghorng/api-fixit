<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\OwnerPayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\OwnerPayoutPaidMail;
use App\Models\Owner;

class OwnerPayoutController extends Controller
{
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

    public function show($id)
    {
        $payout = OwnerPayout::with(['owner', 'split.payment'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Owner payout fetched successfully',
            'data' => $payout,
        ]);
    }

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
    public function payMultipleAndSendEmail(Request $request)
    {
        $request->validate([
            'owner_id' => 'required|exists:owners,id',
            'payout_ids' => 'required|array|min:1',
            'payout_ids.*' => 'required|integer|exists:owner_payouts,id',
            'method' => 'required|in:bank_transfer,card,cash,bakong,khqr',
            'transaction_reference' => 'required|string|max:255',
        ]);
    
        $owner = Owner::with('user')->findOrFail($request->owner_id);
    
        $payouts = OwnerPayout::with(['split.payment', 'owner.user'])
            ->where('owner_id', $request->owner_id)
            ->whereIn('id', $request->payout_ids)
            ->where('status', 'pending')
            ->get();
    
        if ($payouts->count() !== count($request->payout_ids)) {
            return response()->json([
                'success' => false,
                'message' => 'Some payouts are invalid, already paid, or do not belong to this owner.',
            ], 422);
        }
    
        $totalAmount = $payouts->sum('amount');
    
        DB::transaction(function () use ($payouts, $request) {
            foreach ($payouts as $payout) {
                $payout->update([
                    'method' => $request->method,
                    'status' => 'paid',
                    'transaction_reference' => $request->transaction_reference,
                    'paid_at' => now(),
                ]);
            }
        });
    
        if ($owner->user && $owner->user->email) {
            Mail::to($owner->user->email)->send(
                new OwnerPayoutPaidMail(
                    $owner,
                    $payouts,
                    $totalAmount,
                    $request->transaction_reference
                )
            );
        }
    
        return response()->json([
            'success' => true,
            'message' => 'Owner payouts paid and email sent successfully.',
            'data' => [
                'owner_id' => $owner->id,
                'owner_email' => $owner->user->email ?? null,
                'total_payouts' => $payouts->count(),
                'total_amount' => round((float) $totalAmount, 2),
                'transaction_reference' => $request->transaction_reference,
                'items' => $payouts->map(function ($payout) {
                    $payment = $payout->split->payment ?? null;
    
                    return [
                        'payout_id' => $payout->id,
                        'payment_id' => $payment->id ?? null,
                        'user_id' => $payment->user_id ?? null,
                        'service_booking_id' => $payment->service_booking_id ?? null,
                        'amount' => $payout->amount,
                        'method' => $payout->method,
                        'status' => $payout->status,
                    ];
                }),
            ],
        ]);
    }
}