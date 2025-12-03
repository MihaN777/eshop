<?php

use App\Services\Payments\UnitPay;
use App\Services\Payments\YooKassa;

return [

    'providers' => [
        'yoo_kassa' => [
            'class' => YooKassa::class,
            'key' => env('PAYMENT_YOOKASSA_KEY', ''),
            'shop_id' => env('PAYMENT_YOOKASSA_SHOP_ID', ''),
        ],
    ],

];
