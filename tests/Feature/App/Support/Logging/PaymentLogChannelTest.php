<?php

namespace Tests\Feature\App\Support\Logging;

use App\Jobs\SendTelegramLogJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PaymentLogChannelTest extends TestCase
{
    /**
     * Канал payment — стек из файлового лога и Telegram. Доставка в Telegram
     * идёт через очередь: запись в канал ставит джобу с готовым текстом.
     */
    public function test_payment_channel_queues_telegram_delivery(): void
    {
        config()->set('logging.channels.telegram.token', 'test-token');
        config()->set('logging.channels.telegram.chat_id', '123');

        Queue::fake();

        Log::channel('payment')->warning('Проверка канала #777');

        Queue::assertPushed(
            SendTelegramLogJob::class,
            fn (SendTelegramLogJob $job) => $job->token === 'test-token'
                && $job->chatId === 123
                && str_contains($job->message, '#777')
        );
    }
}
