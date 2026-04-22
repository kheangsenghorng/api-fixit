<?php

namespace App\Events;

use App\Models\Payment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $action,
        public ?array $data,
        public int $id
    ) {
    }

    public static function fromModel(string $action, Payment $payment): self
    {
        $payment->loadMissing([
            'user',
            'owner',
            'serviceBooking',
            'coupon',
        ]);

        return new self(
            action: $action,
            data: $payment->toArray(),
            id: $payment->id,
        );
    }

    public static function fromDeletedModel(Payment $payment): self
    {
        return new self(
            action: 'deleted',
            data: null,
            id: $payment->id,
        );
    }

    public function broadcastOn(): array
    {
        return [new Channel('payments')];
    }

    public function broadcastAs(): string
    {
        return 'payment.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'id' => $this->id,
            'data' => $this->data,
        ];
    }
}