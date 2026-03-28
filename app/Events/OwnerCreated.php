<?php

namespace App\Events;

use App\Models\Owner;
use App\Http\Resources\OwnerResource;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OwnerCreated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public Owner $owner)
    {
    }

    public function broadcastOn(): array
    {
        return [new Channel('owners')];
    }

    public function broadcastAs(): string
    {
        return 'owner.created';
    }

    public function broadcastWith(): array
    {
        return [
            'owner' => OwnerResource::make($this->owner)->resolve(),
            'ownerId' => $this->owner->id,
        ];
    }
}