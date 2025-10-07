<?php

namespace App\Notifications\AuthCustom;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends VerifyEmail implements ShouldQueue
{
    use Queueable;

    //    public function __construct()
    //    {
    //        // Переопределить очередь
    //        $this->queue = 'verify';
    //
    //        // Переопределить соединение
    //        $this->connection = 'verify';
    //    }

    protected function buildMailMessage($url): MailMessage
    {
        $appName = config('app.name');

        return (new MailMessage)
            ->subject("{$appName}: Подтверждение электронной почты")
            ->line('Нажмите кнопку ниже, чтобы подтвердить свой адрес электронной почты.')
            ->action('Подтвердить', $url)
            ->line('Если вы не создавали учетную запись, никаких дальнейших действий не требуется.');
    }
}
