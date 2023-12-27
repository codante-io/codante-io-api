<?php

return [
    "channels" => [
        "notificacoes" => env("DISCORD_WEBHOOK_NOTIFICACOES"),
        "notificacoes-site" => env("DISCORD_WEBHOOK_NOTIFICACOES_SITE"),
        "notificacoes-compras" => env("DISCORD_WEBHOOK_NOTIFICACOES_COMPRAS"),
        "submissoes" => env("DISCORD_WEBHOOK_SUBMISSOES"),
        "comunicados" => env("DISCORD_WEBHOOK_COMUNICADOS"),
        "bugs" => env("DISCORD_WEBHOOK_BUGS"),
        "pedidos-certificados" => env("DISCORD_WEBHOOK_PEDIDOS_CERTIFICADOS"),
    ],
];
