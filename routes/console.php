<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('model:prune')->daily();

// Остатки удерживаются неоплаченным заказом до ~20 мин.
// Ниже опускать нельзя — клиент, ушедший на страницу оплаты, должен успеть заплатить до отмены заказа.
Schedule::command('eshop:cancel-unpaid-orders --minutes=15')->everyFiveMinutes();
