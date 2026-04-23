<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OwnerNotificationEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $ownerId,
        public string $type,
        public array $data
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('owner.' . $this->ownerId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'owner.notification';
    }

    public function broadcastWith(): array
    {
        return [
            'type' => $this->type,
            'data' => $this->data,
            'ownerId' => $this->ownerId,
        ];
    }
}