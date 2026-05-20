<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'wallet_transaction_id' => $this->wallet_transaction_id,
            'wallet_id' => $this->wallet_id,
            'user_id' => $this->user_id,
            'payment_id' => $this->payment_id,
            'service_booking_id' => $this->service_booking_id,

            'type' => $this->type,
            'method' => $this->method,
            'transaction_ref' => $this->transaction_ref,
            'external_transaction_id' => $this->external_transaction_id,

            'amount' => $this->amount,
            'balance_before' => $this->balance_before,
            'balance_after' => $this->balance_after,
            'description' => $this->description,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}