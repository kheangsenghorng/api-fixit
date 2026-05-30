<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotificationService
{
    public function sendBookingToCompany($booking)
    {
        $owner = $booking->service->owner ?? null;

        if (!$owner || !$owner->telegram_group_id) {
            Log::warning('Telegram group not connected', [
                'booking_id' => $booking->id,
            ]);

            return false;
        }

        $serviceName =
            $booking->service->title ??
            $booking->service->name ??
            'Unknown Service';

        $packageName =
            $booking->package->name ??
            'Standard Package';

        $text = "🛠 New Booking - Service Fixit\n\n"
            . "Booking ID: #{$booking->id}\n"
            . "Service: {$serviceName}\n"
            . "Package: {$packageName}\n"
            . "Customer: {$booking->user->name}\n"
            . "Phone: " . ($booking->user->phone ?? 'N/A') . "\n"
            . "Date: {$booking->booking_date}\n"
            . "Hours: {$booking->booking_hours}\n\n"
            . "Please check dashboard and assign provider.";

        $response = Http::post(
            "https://api.telegram.org/bot" .
                config('services.telegram.bot_token') .
                "/sendMessage",
            [
                'chat_id' => $owner->telegram_group_id,
                'text' => $text,
            ]
        );

        Log::info('Telegram booking notification', [
            'booking_id' => $booking->id,
            'group_id' => $owner->telegram_group_id,
            'response' => $response->json(),
        ]);

        return $response->successful();
    }
}