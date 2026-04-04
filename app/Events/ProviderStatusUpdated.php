<?php

namespace App\Events;

use App\Models\ServiceBookingProvider;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProviderStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ServiceBookingProvider $bookingProvider;

    public function __construct(ServiceBookingProvider $bookingProvider)
    {
        $this->bookingProvider = $bookingProvider->load([
            'serviceBooking.user',
            'provider',
        ]);
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('service-booking.' . $this->bookingProvider->service_booking_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'provider.status.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->bookingProvider->id,
            'service_booking_id' => $this->bookingProvider->service_booking_id,
            'provider_id' => $this->bookingProvider->provider_id,
            'status' => $this->bookingProvider->status,
            'completed_at' => $this->bookingProvider->completed_at,
            'updated_at' => $this->bookingProvider->updated_at,
        ];
    }
}