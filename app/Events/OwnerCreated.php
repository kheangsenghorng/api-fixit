<?php

namespace App\Events;

use App\Models\Owner;
use App\Http\Resources\OwnerResource;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OwnerCreated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public Owner $owner;

    public function __construct(Owner $owner)
    {
        $this->owner = $owner;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.notifications'), // only admin listens here
        ];
    }

    public function broadcastAs(): string
    {
        return 'owner.created';
    }

    public function broadcastWith(): array
    {
        return [
            'owner' => new OwnerResource($this->owner),
            'message' => 'A new owner has been created.',
            'created_at' => now()->toDateTimeString(),
        ];
    }
}