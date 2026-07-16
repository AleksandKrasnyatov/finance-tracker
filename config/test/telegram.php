<?php

declare(strict_types=1);

use App\Infrastructure\Bot\Telegram\NutgramContainer;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use SergiX44\Nutgram\Configuration;
use SergiX44\Nutgram\Nutgram;

return [
    Nutgram::class => static function (ContainerInterface $container): Nutgram {
        return Nutgram::fake(
            config: new Configuration(
                container: $container->get(NutgramContainer::class),
                cache: $container->get(CacheInterface::class),
                logger: $container->get(LoggerInterface::class),
            ),
        );
    },
];
