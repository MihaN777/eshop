<?php

return [

    'providers' => [
        'yoo_kassa' => [
            'key' => env('YOOKASSA_KEY', ''),
            'shop_id' => env('YOOKASSA_SHOP_ID', ''),
        ],

        'unit_pay' => [
            'key' => '',
        ],
    ],

];
