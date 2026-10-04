<?php

return [
    'sources' => [
        'forge' => 'Laravel Forge',
    ],
    'destinations' => [
        'google_chat' => [
            'label' => 'Google Chat',
            'host' => 'chat.googleapis.com',
        ],
    ],
    'admin_path' => env('CHISMOSA_ADMIN_PATH', 'dKL2596a4xdMrVYizZs3Z46f'),
];
