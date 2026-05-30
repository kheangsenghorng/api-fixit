<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TelegramNotificationService
{
    public function sendBookingToCompany($booking)
    {
        $owner = $booking->service->owner ?? null;

        if (!$owner || !$owner->telegram_group_id) {
            return false;
        }

        $text = "🛠 New Booking - Service Fixit\n\n"
            . "Booking ID: #{$booking->id}\n"
            . "Service: {$booking->service->title}\n"
            . "Package: {$booking->package->name}\n"
            . "Customer: {$booking->user->name}\n"
            . "Phone: {$booking->user->phone}\n"
            . "Date: {$booking->booking_date}\n"
            . "Time: {$booking->booking_time}\n\n"
            . "Please check dashboard and assign provider.";

        return Http::post(
            "https://api.telegram.org/bot" . config('services.telegram.bot_token') . "/sendMessage",
            [
                'chat_id' => $owner->telegram_group_id,
                'text' => $text,
            ]
        );
    }
}