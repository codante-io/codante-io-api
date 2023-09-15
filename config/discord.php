<?php

return [
    'channels' => [
        'notificacoes' => env('DISCORD_WEBHOOK_NOTIFICACOES'),
        'notificacoes-site' => env('DISCORD_WEBHOOK_NOTIFICACOES_SITE'),
        'bugs' => env('DISCORD_WEBHOOK_BUGS')
    ],
];
