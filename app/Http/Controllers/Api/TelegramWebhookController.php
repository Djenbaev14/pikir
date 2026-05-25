<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TelegramChat;
use App\Support\Telegram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    /**
     * Telegram webhook. Bot guruhga qo'shilganda / chiqarilganda
     * chat_id ni avtomatik saqlaydi yoki faolsizlantiradi.
     */
    public function handle(Request $request, string $secret)
    {
        // Faqat bizning maxfiy kalit bilan kelgan so'rovni qabul qilamiz.
        if (!hash_equals(Telegram::webhookSecret(), $secret)) {
            abort(403);
        }

        $update = $request->all();

        // "my_chat_member" — botning o'zining guruhdagi holati o'zgarganda keladi.
        if (isset($update['my_chat_member'])) {
            $event = $update['my_chat_member'];
            $chat = $event['chat'] ?? [];
            $status = $event['new_chat_member']['status'] ?? null;
            $type = $chat['type'] ?? null;

            if (in_array($type, ['group', 'supergroup'], true) && isset($chat['id'])) {
                if (in_array($status, ['member', 'administrator'], true)) {
                    // Bot guruhga qo'shildi -> chat_id ni saqlaymiz.
                    TelegramChat::updateOrCreate(
                        ['chat_id' => (string) $chat['id']],
                        [
                            'title' => $chat['title'] ?? null,
                            'type' => $type,
                            'is_active' => true,
                        ]
                    );
                    Log::info('Telegram guruh ulandi', [
                        'chat_id' => $chat['id'],
                        'title' => $chat['title'] ?? null,
                    ]);
                } elseif (in_array($status, ['left', 'kicked'], true)) {
                    // Bot guruhdan chiqarildi -> faolsizlantiramiz.
                    TelegramChat::where('chat_id', (string) $chat['id'])
                        ->update(['is_active' => false]);
                    Log::info('Telegram guruh o\'chirildi', ['chat_id' => $chat['id']]);
                }
            }
        }

        return response()->json(['ok' => true]);
    }
}
