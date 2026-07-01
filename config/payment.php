<?php

use App\Services\Payments\YooKassa;
use Tests\Support\Payment\FakePaymentProvider;

return [

    'providers' => [
        'production' => [
            'yoo_kassa' => [
                'class' => YooKassa::class,
                'key' => env('PAYMENT_YOOKASSA_KEY', ''),
                'shop_id' => env('PAYMENT_YOOKASSA_SHOP_ID', ''),
            ],
        ],

        'testing' => [
            'fake' => [
                'class' => FakePaymentProvider::class,
                'key' => env('PAYMENT_FAKE_KEY', ''),
                'shop_id' => env('PAYMENT_FAKE_SHOP_ID', ''),
            ],
        ],
    ],

];
