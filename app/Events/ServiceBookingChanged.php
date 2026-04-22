<?php

namespace App\Events;

use App\Http\Resources\ServiceBookingResource;
use App\Models\ServiceBooking;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceBookingChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $action,
        public array $serviceBooking
    ) {
    }

    public static function fromModel(string $action, ServiceBooking $serviceBooking): self
    {
        $serviceBooking->loadMissing([
            'user',
            'service.category',
            'service.type',
            'payment',
        ]);

        return new self(
            $action,
            (new ServiceBookingResource($serviceBooking))->resolve()
        );
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('service-bookings'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'service-booking.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'serviceBooking' => $this->serviceBooking,
        ];
    }
}