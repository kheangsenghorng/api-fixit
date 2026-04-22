<?php

namespace App\Observers;

use App\Events\PaymentChanged;
use App\Models\Payment;

class PaymentObserver
{
    public function created(Payment $payment): void
    {
        broadcast(PaymentChanged::fromModel('created', $payment));
    }

    public function updated(Payment $payment): void
    {
        broadcast(PaymentChanged::fromModel('updated', $payment));
    }

    public function deleted(Payment $payment): void
    {
        broadcast(PaymentChanged::fromDeletedModel($payment));
    }
}