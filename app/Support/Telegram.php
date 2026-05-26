<?php

namespace App\Support;

use App\Models\Business;

/**
 * Telegram bot uchun yagona sozlama manbasi.
 *
 * Token va boshqalar avval .env (config) dan, bo'lmasa bazadagi
 * businesses.token dan olinadi. Shu sabab production .env ga
 * hech narsa qo'shmasa ham ishlaydi (bitta bot).
 */
class Telegram
{
    /**
     * Bot tokeni: .env (config) -> aks holda DB dagi birinchi biznes tokeni.
     */
    public static function token(): ?string
    {
        $token = config('services.telegram.token');

        if (!empty($token)) {
            return $token;
        }

        return Business::query()
            ->whereNotNull('token')
            ->where('token', '!=', '')
            ->value('token');
    }

    /**
     * Proxy (yoki to'g'ridan-to'g'ri) Telegram API manzili.
     */
    public static function apiBase(): string
    {
        return rtrim((string) config('services.telegram.api_base'), '/');
    }

    /**
     * Telegram so'rovlari uchun HTTP klient.
     *
     * Worker (api_base) Cloudflare'da bo'lib, to'g'ridan ochiladi.
     * Shu sabab serverdagi flaky SOCKS proxy (all_proxy/https_proxy env)
     * ni chetlab o'tamiz — aks holda u o'chganda xabar yuborilmay qoladi.
     */
    public static function http()
    {
        return \Illuminate\Support\Facades\Http::withOptions(['proxy' => '']);
    }

    /**
     * Webhook URL ichidagi maxfiy kalit.
     * .env da bo'lmasa, tokendan deterministik tarzda hosil qilinadi.
     */
    public static function webhookSecret(): string
    {
        $secret = config('services.telegram.webhook_secret');

        if (!empty($secret)) {
            return (string) $secret;
        }

        return substr(hash('sha256', (string) self::token()), 0, 32);
    }
}
