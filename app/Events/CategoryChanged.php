<?php

namespace App\Events;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CategoryChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $action;
    public Category $category;

    public function __construct(string $action, Category $category)
    {
        $this->action = $action;
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
        return 'category.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'category' => (new CategoryResource($this->category))->resolve(),
        ];
    }
}