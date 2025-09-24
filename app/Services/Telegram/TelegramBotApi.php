<?php

namespace App\Services\Telegram;

use App\Services\Telegram\Exceptions\TelegramBotApiException;
use Illuminate\Support\Facades\Http;
use Throwable;

class TelegramBotApi
{
    // https://api.telegram.org/botXXXXXXtokenXXXXXXX/getUpdates - bot info
    public const HOST = 'https://api.telegram.org/bot';

    public static function sendMessage(string $token, int $chatId, string $message = ''): bool
    {
        try {
            $response = Http::get(self::HOST . $token . '/sendMessage',
                [
                    'chat_id' => $chatId,
                    'text' => $message,
                ])
                ->throw()
                ->json();

            return $response['ok'] ?? false;
        } catch (Throwable $e) {
            report(new TelegramBotApiException($e->getMessage()));
            return false;
        }
    }
}
