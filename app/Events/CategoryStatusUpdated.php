<?php

namespace App\Events;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CategoryStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $category;

    public function __construct(Category $category)
    {
        $this->category = $category;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('categories'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'category.status.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'category' => (new CategoryResource($this->category))->resolve(),
        ];
    }
}