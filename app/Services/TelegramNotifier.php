<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TelegramNotifier
{
    public static function send(string $message, string $parseMode = 'HTML', ?array $replyMarkup = null): void
    {
        $token = config('services.telegram.bot_token');
        $chatId = config('services.telegram.chat_id');

        if (!$token || !$chatId) {
            throw new \RuntimeException('Telegram bot token or chat ID is missing.');
        }

        $payload = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => $parseMode,
        ];

        if ($replyMarkup) {
            $payload['reply_markup'] = json_encode($replyMarkup);
        }

        $response = Http::post("https://api.telegram.org/bot{$token}/sendMessage", $payload);

        if (!$response->successful()) {
            throw new \RuntimeException($response->body());
        }
    }
}