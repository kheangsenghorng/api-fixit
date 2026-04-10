<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCouponUsageRequest;
use App\Http\Requests\UpdateCouponUsageRequest;
use App\Http\Resources\CouponUsageResource;
use App\Models\Coupon;
use App\Models\CouponUsage;

class CouponUsageController extends Controller
{
    public function index()
    {
        $couponUsages = CouponUsage::with('coupon')
            ->select('coupon_id')
            ->selectRaw('COUNT(DISTINCT user_id) as users_count')
            ->selectRaw('SUM(times_used) as total_times_used')
            ->groupBy('coupon_id')
            ->latest('coupon_id')
            ->paginate(10);
    
        return response()->json($couponUsages);
    }

    public function topPerformingCoupons()
    {
        $couponUsages = CouponUsage::with('coupon')
            ->select('coupon_id')
            ->selectRaw('COUNT(DISTINCT user_id) as users_count')
            ->selectRaw('SUM(times_used) as total_times_used')
            ->groupBy('coupon_id')
            ->orderByDesc('total_times_used')
            ->take(3)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'top_1' => $couponUsages->get(0),
                'top_2' => $couponUsages->get(1),
                'top_3' => $couponUsages->get(2),
            ]
        ]);
    }


    public function store(StoreCouponUsageRequest $request)
    {
        $usage = CouponUsage::where('coupon_id', $request->coupon_id)
            ->where('user_id', $request->user_id)
            ->first();
    
        $increaseBy = $request->times_used ?? 1;
    
        if ($usage) {
            $usage->update([
                'times_used' => $usage->times_used + $increaseBy,
            ]);
        } else {
            $usage = CouponUsage::create([
                'coupon_id' => $request->coupon_id,
                'user_id' => $request->user_id,
                'times_used' => $increaseBy,
            ]);
        }
    
        $usage->load(['coupon', 'user']);
    
        return response()->json([
            'success' => true,
            'message' => 'Coupon usage saved successfully',
            'data' => new CouponUsageResource($usage)
        ], 201);
    }

    public function show(CouponUsage $couponUsage)
    {
        $couponUsage->load(['coupon', 'user']);

        return new CouponUsageResource($couponUsage);
    }

    public function update(UpdateCouponUsageRequest $request, CouponUsage $couponUsage)
    {
        $couponUsage->update($request->validated());
    
        $couponUsage->load(['coupon', 'user']);
    
        return response()->json([
            'success' => true,
            'message' => 'Coupon usage updated successfully',
            'data' => new CouponUsageResource($couponUsage)
        ]);
    }
    public function destroy(CouponUsage $couponUsage)
    {
        $couponUsage->delete();

        return response()->json([
            'message' => 'Coupon usage deleted successfully.',
        ]);
    }
}