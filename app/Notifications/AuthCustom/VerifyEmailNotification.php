<?php

namespace App\Notifications\AuthCustom;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

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
}
