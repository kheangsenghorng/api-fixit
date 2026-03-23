<?php

namespace App\Events;

use App\Http\Resources\TypeResource;
use App\Models\Type;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TypeChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $action,
        public ?Type $type = null,
        public ?int $typeId = null
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('types'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'type.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'type' => $this->type
                ? (new TypeResource(
                    $this->type->load('category')
                ))->resolve()
                : null,
            'typeId' => $this->typeId ?? $this->type?->id,
        ];
    }
}