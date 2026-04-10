<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCouponRequest;
use App\Http\Requests\UpdateCouponRequest;
use App\Http\Resources\CouponResource;
use App\Models\Coupon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     */

     
   public function index(Request $request)
{
    $search = $request->input('search');
    $status = $request->input('status');
    $owner_id = $request->input('owner_id'); // 1. Get owner_id from request

    $query = Coupon::with(['owner', 'usages'])
        ->withCount([
            'usages as users_count' => function ($query) {
                $query->select(DB::raw('COUNT(DISTINCT user_id)'));
            }
        ])
        ->withSum('usages as total_times_used', 'times_used');

    // Filter by Unique ID
    if (!empty($search)) {
        $query->where('unique_id', 'LIKE', "%{$search}%");
    }

    // Filter by Status
    if (!empty($status) && $status !== 'all') {
        $query->where('status', $status);
    }

    // 2. Filter by Owner ID (Matches exactly)
    if (!empty($owner_id)) {
        $query->where('owner_id', $owner_id);
    }

    $coupons = $query->orderByDesc('id')->paginate(10);

    $coupons->getCollection()->transform(function ($coupon) {
        $coupon->total_times_used = $coupon->total_times_used ?? 0;
        return $coupon;
    });

    return response()->json($coupons);
}

    // count coupon
    public function stats()
    {
        $stats = Coupon::selectRaw("
            COUNT(*) as total,
            COUNT(CASE WHEN status = 'active' THEN 1 END) as active,
            COUNT(CASE WHEN status = 'expired' THEN 1 END) as expired,
            COUNT(CASE WHEN status = 'disabled' THEN 1 END) as disabled
        ")->first();
    
        return response()->json([
            'success' => true,
            'message' => 'Coupon statistics retrieved successfully.',
            'data' => [
                'total_coupon' => (int) $stats->total,
                'active_coupon' => (int) $stats->active,
                'disabled_coupon' => (int) $stats->disabled,
                'expired_coupon' => (int) $stats->expired,
            ],
        ]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCouponRequest $request)
    {
        $coupon = Coupon::create($request->validated());

        $coupon->load(['owner', 'usages']);

        return new CouponResource($coupon);
    }

    /**
     * Display the specified resource.
     */
    public function show(Coupon $coupon)
    {
        $coupon->load(['owner', 'usages']);

        return new CouponResource($coupon);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCouponRequest $request, Coupon $coupon)
    {
        $coupon->update($request->validated());

        $coupon->load(['owner', 'usages']);

        return new CouponResource($coupon);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return response()->json([
            'message' => 'Coupon deleted successfully.'
        ]);
    }
}