<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use RuntimeException;
use SergiX44\Nutgram\Configuration;
use SergiX44\Nutgram\Nutgram;

final readonly class NutgramFactory
{
    /**
     * @param list<string> $allowedUpdates
     */
    public function __construct(
        private NutgramContainer $container,
        private CacheInterface $cache,
        private LoggerInterface $logger,
        private string $token,
        private array $allowedUpdates,
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function create(): Nutgram
    {
        if ($this->token === '') {
            throw new RuntimeException('TELEGRAM_BOT_TOKEN is not configured.');
        }

        return new Nutgram(
            $this->token,
            new Configuration(
                container: $this->container,
                cache: $this->cache,
                logger: $this->logger,
                pollingAllowedUpdates: $this->allowedUpdates,
            ),
        );
    }
}
