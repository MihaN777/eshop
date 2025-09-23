<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Http;

class TelegramBotApi
{
    // https://api.telegram.org/botXXXXXXtokenXXXXXXX/getUpdates - bot info
    public const HOST = 'https://api.telegram.org/bot';
    public static function sendMessage(string $token, int $chatId, string $message = '') {
        Http::get(self::HOST . $token . '/sendMessage', [
            'chat_id' => $chatId,
            'text' => $message,
        ]);
    }
}
