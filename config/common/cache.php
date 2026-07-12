<?php

declare(strict_types=1);

use Predis\Client;
use Predis\ClientInterface;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Psr16Cache;

return [
    ClientInterface::class => static function (): ClientInterface {
        return new Client([
            'host' => getenv('REDIS_HOST') ?: 'redis',
            'port' => (int)(getenv('REDIS_PORT') ?: 6379),
        ]);
    },
    CacheInterface::class => static function (ContainerInterface $container): CacheInterface {
        return new Psr16Cache(
            new RedisAdapter(
                $container->get(ClientInterface::class),
                'finance_tracker',
            ),
        );
    },
];
