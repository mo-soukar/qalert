<?php


return [
    'channels' => [
        'telegram' => [
            'bot_token' => env('TELEGRAM_BOT_TOKEN'),
            'chat_id' => env('TELEGRAM_CHAT_ID'),
        ]
    ],
    'default_channel' => 'telegram',
    'project'   => env('APP_NAME', 'Unknown Service'),
    'enabled'   => env('QALERT_ENABLED', true),

];