<?php

namespace App\Observers;

use App\Events\ServiceBookingChanged;
use App\Models\ServiceBooking;

class ServiceBookingObserver
{
    public function created(ServiceBooking $serviceBooking): void
    {
        broadcast(ServiceBookingChanged::fromModel('created', $serviceBooking));
    }

    public function updated(ServiceBooking $serviceBooking): void
    {
        broadcast(ServiceBookingChanged::fromModel('updated', $serviceBooking));
    }

    public function deleted(ServiceBooking $serviceBooking): void
    {
        broadcast(ServiceBookingChanged::fromModel('deleted', $serviceBooking));
    }
}