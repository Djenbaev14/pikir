<?php

namespace App\Console\Commands;

use App\Support\Telegram;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SetTelegramWebhook extends Command
{
    /**
     * @var string
     */
    protected $signature = 'telegram:set-webhook {--token= : Bot token (bo\'sh bo\'lsa .env yoki bazadan olinadi)}';

    /**
     * @var string
     */
    protected $description = 'Telegram botiga webhook URL ni o\'rnatadi (Cloudflare proxy orqali)';

    public function handle()
    {
        $token = $this->option('token') ?: Telegram::token();

        if (empty($token)) {
            $this->error('Bot tokeni topilmadi (.env TELEGRAM_BOT_TOKEN ham, businesses.token ham bo\'sh).');
            return Command::FAILURE;
        }

        $base = Telegram::apiBase();
        $secret = Telegram::webhookSecret();

        // URL .env dagi APP_URL dan olinadi.
        $webhookUrl = rtrim((string) config('app.url'), '/') . '/api/telegram/webhook/' . $secret;

        $response = Http::get("{$base}/bot{$token}/setWebhook", [
            'url' => $webhookUrl,
            'allowed_updates' => json_encode(['my_chat_member']),
        ]);

        if ($response->successful() && $response->json('ok') === true) {
            $this->info('Webhook o\'rnatildi: ' . $webhookUrl);
            return Command::SUCCESS;
        }

        $this->error('Webhook o\'rnatilmadi: ' . $response->body());
        $this->line('Ishlatilgan token (boshi): ' . substr((string) $token, 0, 12) . '...');
        $this->line('URL: ' . $webhookUrl);
        return Command::FAILURE;
    }
}
