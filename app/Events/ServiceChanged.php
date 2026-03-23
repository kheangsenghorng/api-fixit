<?php

namespace App\Events;

use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $action,
        public ?Service $service = null,
        public ?int $serviceId = null
    ) {
    }

    public function broadcastOn(): array
    {
        return [new Channel('services')];
    }

    public function broadcastAs(): string
    {
        return 'service.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'service' => $this->service
                ? (new ServiceResource(
                    $this->service->load(['owner', 'category', 'type'])
                ))->resolve()
                : null,
            'serviceId' => $this->serviceId ?? $this->service?->id,
        ];
    }
}