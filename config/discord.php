<?php

return [
    "channels" => [
        "notificacoes" => env("DISCORD_WEBHOOK_NOTIFICACOES"),
        "notificacoes-site" => env("DISCORD_WEBHOOK_NOTIFICACOES_SITE"),
        "notificacoes-compras" => env("DISCORD_WEBHOOK_NOTIFICACOES_COMPRAS"),
        "bugs" => env("DISCORD_WEBHOOK_BUGS"),
    ],
];
