<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Owner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TelegramWebhookController extends Controller
{
    public function webhook(Request $request)
    {
        $update = $request->all();

        /*
        |--------------------------------------------------------------------------
        | Auto detect when bot is added to group
        |--------------------------------------------------------------------------
        */
        if (isset($update['my_chat_member'])) {
            $chat = $update['my_chat_member']['chat'] ?? null;
            $newStatus = $update['my_chat_member']['new_chat_member']['status'] ?? null;

            if (!$chat) {
                return response()->json(['ok' => true]);
            }

            if (in_array($newStatus, ['member', 'administrator'])) {
                return response()->json([
                    'ok' => true,
                    'message' => 'Bot added to group',
                    'telegram_group_id' => $chat['id'],
                    'telegram_group_name' => $chat['title'] ?? null,
                    'note' => 'Now send /connect YOUR_CODE to link this group with company.',
                ]);
            }

            return response()->json(['ok' => true]);
        }

        /*
        |--------------------------------------------------------------------------
        | Connect group with company using /connect CODE
        |--------------------------------------------------------------------------
        */
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

        if (str_starts_with($text, '/connect')) {
            $parts = preg_split('/\s+/', $text);
            $connectCode = $parts[1] ?? null;

            if (!$connectCode) {
                $this->sendMessage($chatId, "❌ Connect code missing.\nExample: /connect FIXIT-RHOC351V");

                return response()->json([
                    'ok' => false,
                    'message' => 'Connect code missing',
                ]);
            }

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
        Http::post('https://api.telegram.org/bot' . env('TELEGRAM_BOT_TOKEN') . '/sendMessage', [
            'chat_id' => $chatId,
            'text' => $text,
        ]);
    }
}