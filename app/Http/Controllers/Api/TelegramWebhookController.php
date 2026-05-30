<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Owner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function webhook(Request $request)
    {
        Log::info('Telegram webhook received', $request->all());

        $update = $request->all();

        if (isset($update['my_chat_member'])) {
            $chat = $update['my_chat_member']['chat'] ?? null;
            $newStatus = $update['my_chat_member']['new_chat_member']['status'] ?? null;

            if ($chat && in_array($newStatus, ['member', 'administrator'])) {
                return response()->json([
                    'ok' => true,
                    'message' => 'Bot added to group',
                    'telegram_group_id' => (string) $chat['id'],
                    'telegram_group_name' => $chat['title'] ?? null,
                    'note' => 'Send /connect YOUR_CODE to link this group with company.',
                ]);
            }

            return response()->json(['ok' => true]);
        }

        $message = $update['message'] ?? null;

        if (!$message) {
            return response()->json(['ok' => true]);
        }

        $chat = $message['chat'] ?? null;
        $text = trim($message['text'] ?? '');

        if (!$chat) {
            return response()->json(['ok' => true]);
        }

        $chatId = (string) $chat['id'];
        $groupName = $chat['title'] ?? null;
        $chatType = $chat['type'] ?? null;

        if (!in_array($chatType, ['group', 'supergroup'])) {
            return response()->json(['ok' => true]);
        }

        // Supports:
        // /connect FIXIT-XXXX
        // /connect@FixitServiceME_bot FIXIT-XXXX
        if (preg_match('/^\/connect(?:@\w+)?\s+(.+)$/', $text, $matches)) {
            $connectCode = trim($matches[1]);

            $owner = Owner::where('telegram_connect_code', $connectCode)->first();

            if (!$owner) {
                $this->sendMessage($chatId, "❌ Invalid connect code.");
                return response()->json([
                    'ok' => false,
                    'message' => 'Invalid connect code',
                ]);
            }

            $owner->update([
                'telegram_group_id' => $chatId,
                'telegram_group_name' => $groupName,
                'telegram_connected' => true,
            ]);

            $this->sendMessage(
                $chatId,
                "✅ Service Fixit bot connected successfully.\nCompany: {$owner->business_name}"
            );

            return response()->json([
                'ok' => true,
                'message' => 'Telegram group connected successfully',
                'telegram_group_id' => $chatId,
                'telegram_group_name' => $groupName,
            ]);
        }

        return response()->json(['ok' => true]);
    }

    private function sendMessage($chatId, $text): void
    {
        $token = config('services.telegram.bot_token');

        if (!$token) {
            Log::error('Telegram bot token missing.');
            return;
        }

        Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $text,
        ]);
    }
}