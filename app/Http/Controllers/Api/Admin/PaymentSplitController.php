<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSplit;
use Illuminate\Http\Request;

class PaymentSplitController extends Controller
{
    public function index(Request $request)
    {
        $query = PaymentSplit::with(['payment', 'owner','ownerPayout']);

        if ($request->filled('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $splits = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Payment splits fetched successfully',
            'data' => $splits,
        ]);
    }

    public function stats(Request $request)
    {
        $query = PaymentSplit::query();

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
            'message' => 'Payment split stats fetched successfully',
            'data' => [
                'total_splits' => $query->count(),
                'total_service_amount' => round((float) $query->sum('service_amount'), 2),
                'total_admin_commission' => round((float) $query->sum('admin_commission'), 2),
                'total_owner_payout' => round((float) $query->sum('owner_payout'), 2),
            ],
        ]);
    }

    public function show($id)
    {
        $split = PaymentSplit::with(['payment', 'owner', 'ownerPayout'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Payment split fetched successfully',
            'data' => $split,
        ]);
    }
}