<?php

namespace App\Support\Logging\Telegram;

use App\Jobs\SendTelegramLogJob;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Monolog\LogRecord;
use Throwable;

class TelegramLoggerHandler extends AbstractProcessingHandler
{
    protected int $chatId;

    protected string $token;

    public function __construct($config)
    {
        $this->chatId = (int)$config['chat_id'];
        $this->token = $config['token'];
        $level = Logger::toMonologLevel($config['level']);

        parent::__construct($level);
    }

    protected function write(LogRecord $record): void
    {
        $date = now()->format('Y-m-d H:i:s');
        $level = strtoupper($record->level->name ?? 'UNDEFINED_LEVEL');

        $message = "[{$date}] {$level}: {$record->message}";

        try {
            // afterCommit() гарантирует, что при откате транзакции ложный алерт не уйдёт,
            // а воркер не подхватит джобу раньше коммита; вне транзакции диспатч идёт сразу.
            SendTelegramLogJob::dispatch($this->token, $this->chatId, $message)->afterCommit();
        } catch (Throwable) {
            // Недоступность очереди не ломае основной поток и запись в остальные каналы стека.
        }
    }
}
