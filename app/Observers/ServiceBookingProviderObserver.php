<?php

namespace App\Observers;

use App\Events\ProviderStatusUpdated;
use App\Models\ServiceBookingProvider;
use Illuminate\Support\Carbon;

class ServiceBookingProviderObserver
{
    public function updated(ServiceBookingProvider $serviceBookingProvider): void
    {
        if ($serviceBookingProvider->wasChanged('status')) {
            $booking = $serviceBookingProvider->serviceBooking;

            if ($serviceBookingProvider->status === 'completed') {
                $serviceBookingProvider->completed_at = $serviceBookingProvider->completed_at ?? now();
                $serviceBookingProvider->saveQuietly();

                if ($booking) {
                    $booking->update([
                        'booking_status' => 'awaiting_customer_confirmation',
                        'customer_status' => 'pending',
                        'auto_complete_at' => Carbon::now()->addHours(48),
                    ]);
                }
            } elseif (in_array($serviceBookingProvider->status, ['accepted', 'on_the_way', 'arrived', 'working'])) {
                if ($booking && $booking->booking_status !== 'completed') {
                    $booking->update([
                        'booking_status' => 'in_progress',
                    ]);
                }
            }

            broadcast(new ProviderStatusUpdated($serviceBookingProvider))->toOthers();
        }
    }
}