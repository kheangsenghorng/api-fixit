<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Owner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TelegramController extends Controller
{
    public function groupLink(Request $request)
    {
        $user = auth()->user();

        $owner = Owner::where('user_id', $user->id)->first();

        if (!$owner) {
            return response()->json([
                'success' => false,
                'message' => 'Owner profile not found.',
            ], 404);
        }

        if (!$owner->telegram_connect_code) {
            $owner->update([
                'telegram_connect_code' => 'FIXIT-' . strtoupper(Str::random(8)),
            ]);
        }

        $botUsername = env('TELEGRAM_BOT_USERNAME');

        $link = 'https://t.me/' . $botUsername
            . '?startgroup=' . $owner->telegram_connect_code;

        return response()->json([
            'success' => true,
            'message' => 'Telegram group link generated successfully',
            'data' => [
                'telegram_link' => $link,
                'connect_code' => $owner->telegram_connect_code,
                'telegram_connected' => $owner->telegram_connected,
                'telegram_group_id' => $owner->telegram_group_id,
                'telegram_group_name' => $owner->telegram_group_name,
            ],
        ]);
    }

    public function syncGroupId(Request $request)
    {
        $user = auth()->user();

        $owner = Owner::where('user_id', $user->id)->first();

        if (!$owner) {
            return response()->json([
                'success' => false,
                'message' => 'Owner profile not found.',
            ], 404);
        }

        $token = env('TELEGRAM_BOT_TOKEN');

        $response = Http::get("https://api.telegram.org/bot{$token}/getUpdates");

        if (!$response->successful()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot call Telegram API.',
                'error' => $response->body(),
            ], 500);
        }

        $updates = $response->json('result') ?? [];

        $latestGroup = null;

        foreach (array_reverse($updates) as $update) {
            $message = $update['message'] ?? null;
            $chat = $message['chat'] ?? null;

            if (!$chat) {
                continue;
            }

            if (in_array($chat['type'] ?? null, ['group', 'supergroup'])) {
                $latestGroup = [
                    'id' => (string) $chat['id'],
                    'title' => $chat['title'] ?? null,
                    'type' => $chat['type'],
                ];
                break;
            }
        }

        if (!$latestGroup) {
            return response()->json([
                'success' => false,
                'message' => 'No Telegram group found. Add bot to group and send any message first.',
            ], 404);
        }

        $owner->update([
            'telegram_group_id' => $latestGroup['id'],
            'telegram_group_name' => $latestGroup['title'],
            'telegram_connected' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Telegram group ID synced successfully.',
            'data' => [
                'telegram_group_id' => $owner->telegram_group_id,
                'telegram_group_name' => $owner->telegram_group_name,
                'telegram_connected' => $owner->telegram_connected,
            ],
        ]);
    }
}