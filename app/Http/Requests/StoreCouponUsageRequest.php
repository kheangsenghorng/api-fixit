<?php

namespace App\Http\Requests;

use App\Models\Coupon;
use App\Models\CouponUsage;
use Illuminate\Foundation\Http\FormRequest;

class StoreCouponUsageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'coupon_id' => [
                'required',
                'exists:coupons,id',
            ],
            'user_id' => [
                'required',
                'exists:users,id',
            ],
            'times_used' => [
                'nullable',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    $couponId = $this->coupon_id;
                    $userId = $this->user_id;
                    $increaseBy = $value ?? 1;

                    $coupon = Coupon::find($couponId);

                    if (!$coupon) {
                        return;
                    }

                    if ($coupon->status !== 'active') {
                        $fail('This coupon is not active.');
                        return;
                    }

                    if (!is_null($coupon->expires_at) && now()->gt($coupon->expires_at)) {
                        $fail('This coupon has expired.');
                        return;
                    }

                    $usage = CouponUsage::where('coupon_id', $couponId)
                        ->where('user_id', $userId)
                        ->first();

                    $currentUserUsed = $usage?->times_used ?? 0;
                    $newUserTotal = $currentUserUsed + $increaseBy;

                    if (!is_null($coupon->max_uses_per_user) && $newUserTotal > $coupon->max_uses_per_user) {
                        $fail('You have reached the maximum number of uses for this coupon.');
                        return;
                    }

                    $totalUsed = CouponUsage::where('coupon_id', $couponId)->sum('times_used');
                    $newCouponTotal = $totalUsed + $increaseBy;

                    if (!is_null($coupon->max_uses) && $newCouponTotal > $coupon->max_uses) {
                        $fail('This coupon has reached its maximum usage limit.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'coupon_id.required' => 'Coupon is required.',
            'coupon_id.exists' => 'Selected coupon does not exist.',

            'user_id.required' => 'User is required.',
            'user_id.exists' => 'Selected user does not exist.',

            'times_used.integer' => 'Times used must be a number.',
            'times_used.min' => 'Times used must be at least 1.',
        ];
    }
}