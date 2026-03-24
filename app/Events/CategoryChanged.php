<?php

namespace App\Events;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CategoryChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $action,
        public array $category
    ) {
    }

    public static function fromModel(string $action, Category $category): self
    {
        return new self(
            $action,
            (new CategoryResource($category))->resolve()
        );
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('categories'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'category.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'category' => $this->category,
        ];
    }
}