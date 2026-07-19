<?php

namespace App\Jobs;

use App\Services\Telegram\TelegramBotApi;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendTelegramLogJob implements ShouldQueue
{
    use Queueable;

    /**
     * Число попыток и задержки между ними: транзиентные сбои Telegram ретраим.
     *
     * @var array<int, int>
     */
    public array $backoff = [10, 30];

    public int $tries = 3;

    public function __construct(
        public string $token,
        public int $chatId,
        public string $message,
    ) {}

    public function handle(): void
    {
        TelegramBotApi::sendMessage($this->token, $this->chatId, $this->message);
    }

    /**
     * Финальный провал доставки логируем в файловый канал, но НЕ в telegram/payment —
     * иначе получим рекурсию «лог о падении → новая джоба → падение → …».
     */
    public function failed(Throwable $e): void
    {
        Log::channel('single')->error('SendTelegramLogJob: '.$e->getMessage());
    }
}
