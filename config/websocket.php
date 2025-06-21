<?php

return [
    'client' => [
        'callback_class' => \App\Utils\Workerman\WorkerCallback::class,
        'process_num' => 9,
    ],
    'alltick_client' => [
        'callback_class' => \App\Utils\Workerman\WorkerAlltickCallback::class,
        'process_num' => 1,
        'gp_url' => 'https://quote.tradeswitcher.com/quote-stock-b-api', // 股票
        'other_url' => 'https://quote.tradeswitcher.com/quote-b-api', // 外汇，贵金属等
        'token' => '938bc995f89d77dba026e81a44f2ed28-c-app'
    ]
];
