<?php

namespace App\Events;

use App\Http\Resources\CouponUsageResource;
use App\Models\CouponUsage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CouponUsageUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public CouponUsage $couponUsage;
    public string $action;

    public function __construct(CouponUsage $couponUsage, string $action)
    {
        $this->couponUsage = $couponUsage->load(['coupon', 'user']);
        $this->action = $action;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('coupon-usages'),
            new Channel('coupon.' . $this->couponUsage->coupon_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'coupon.usage.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'data' => (new CouponUsageResource($this->couponUsage))->resolve(),
        ];
    }
}