<?php

namespace App\Notifications\AuthCustom;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword implements ShouldQueue
{
    use Queueable;

    //        public function __construct($token)
    //        {
    //            // Переопределить очередь
    //            $this->queue = 'reset';
    //
    //            // Переопределить соединение
    //            $this->connection = 'reset';
    //
    //            parent::__construct($token);
    //        }

    protected function buildMailMessage($url): MailMessage
    {
        $appName = config('app.name');
        $expire = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire');

        return (new MailMessage)
            ->subject("{$appName}: Обновление пароля")
            ->line('Вы получили это электронное письмо, потому что был получен запрос на обновление пароля для вашей учетной записи.')
            ->action('Обновить', $url)
            ->line("Срок действия этой ссылки для обновления пароля истечет через {$expire} минут.")
            ->line('Если вы не запрашивали обновление пароля, никаких дальнейших действий не требуется.');
    }
}
