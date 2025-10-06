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
    //            $this->queue = 'verify';
    //
    //            // Переопределить соединение
    //            $this->connection = 'verify';
    //
    //            parent::__construct($token);
    //        }
}
