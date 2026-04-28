<?php

namespace App\Events;

use App\Http\Resources\ServiceBookingProviderResource;
use App\Models\ServiceBookingProvider;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceBookingProviderChanged implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public string $action;
    public array $data;

    public function __construct(ServiceBookingProvider $serviceBookingProvider, string $action)
    {
        $this->action = $action;

        $serviceBookingProvider->load([
            'serviceBooking.user',
            'serviceBooking.service',
            'provider',
            'assignedByOwner',
        ]);

        $this->data = [
            'action' => $action,
            'item' => new ServiceBookingProviderResource($serviceBookingProvider),
        ];
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('service-booking-providers'),
            new Channel('service-booking.' . $this->data['item']->service_booking_id),
            new Channel('provider.' . $this->data['item']->provider_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'service-booking-provider.changed';
    }
}