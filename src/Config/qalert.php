<?php


return [
    'channels' => [
        'telegram' => [
            'bot_token' => env('TELEGRAM_BOT_TOKEN'),
            'chat_id' => env('TELEGRAM_CHAT_ID'),
        ]
    ],
    'heartbeat' => [
        'heartbeat_interval' => env('HEARTBEAT_INTERVAL' , 1),
        'threshold' => env('HEARTBEAT_THRESHOLD' , 3),
        'check_interval' => env('HEARTBEAT_CHECK_INTERVAL' , 2),
        'watchers' => [
            [
                'queues' => 'default',
                'connection' => 'database',
            ]
        ]
    ],
    'default_channel' => 'telegram',
    'project'   => env('APP_NAME', 'Unknown Service'),
    'enabled'   => env('QALERT_ENABLED', true),

];