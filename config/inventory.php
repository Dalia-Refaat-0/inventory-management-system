<?php

declare(strict_types=1);

return [
    'admin_email' => env('INVENTORY_ADMIN_EMAIL', 'admin@example.com'),

    'cache' => [
        'enabled' => env('INVENTORY_CACHE_ENABLED', true),
        'store'   => env('INVENTORY_CACHE_STORE', 'redis'),

        'cache_expirations' => [
            'warehouse_expiration_seconds' => (int) env('WAREHOUSE_CATALOG_EXPIRATION_SECONDS', 3600),
            'stock_inventory_seconds'   => (int) env('STOCK_INVENTORY_EXPIRATION_SECONDS', 600),
        ],

        'lock' => [
            'wait'    => 5,
            'timeout' => 10,
        ],
    ],
];

