<?php

declare(strict_types=1);

return [
    'config' => [
        'doctrine' => [
            'dev_mode' => true,
            'cache_dir' => null,
            'proxy_dir' => __DIR__ . '/../../var/cache/' . PHP_SAPI . '/doctrine/proxy',
            'connection' => [
                'driver' => 'pdo_sqlite',
                'path' => __DIR__ . '/../../var/test.db',
                'memory' => false,
                'host' => null,
                'user' => null,
                'password' => null,
                'dbname' => null,
                'charset' => 'utf-8',
            ],
        ],
    ],
];
