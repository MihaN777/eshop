<?php

namespace Tests\Feature\App\Jobs;

use App\Jobs\SendTelegramLogJob;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Tests\TestCase;

class SendTelegramLogJobTest extends TestCase
{
    /**
     * handle() отправляет сообщение в Telegram Bot API.
     */
    public function test_handle_sends_message_to_telegram(): void
    {
        Http::fake();

        (new SendTelegramLogJob('test-token', 123, 'Тест доставки'))->handle();

        Http::assertSent(fn ($request) => str_contains($request->url(), 'api.telegram.org')
            && str_contains($request->url(), 'test-token'));
    }

    /**
     * Финальный провал логируется в файловый канал single, но не в telegram/payment —
     * иначе падение доставки порождало бы новую джобу доставки (рекурсия).
     */
    public function test_failed_logs_to_single_channel_not_telegram(): void
    {
        Log::shouldReceive('channel')->with('single')->once()->andReturnSelf();
        Log::shouldReceive('error')->once();

        (new SendTelegramLogJob('test-token', 123, 'msg'))->failed(new RuntimeException('boom'));
    }
}
