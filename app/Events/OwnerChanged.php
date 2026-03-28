<?php

namespace App\Events;

use App\Models\Owner;
use App\Http\Resources\OwnerResource;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OwnerChanged implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $action,
        public ?Owner $owner = null,
        public ?int $ownerId = null
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('admin.notifications');
    }

    public function broadcastAs(): string
    {
        return 'owner.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'action'  => $this->action,
            'owner'   => $this->owner
                ? OwnerResource::make($this->owner->load('user'))->resolve()
                : null,
            'ownerId' => $this->ownerId ?? $this->owner?->id,
        ];
    }
}