<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\SimpleCache\CacheInterface;
use RuntimeException;
use SergiX44\Nutgram\Configuration;
use SergiX44\Nutgram\Nutgram;

final readonly class TelegramBotFactory
{
    public function __construct(
        private ContainerInterface $container,
        private CacheInterface $cache,
        private string $token,
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function create(): TelegramBot
    {
        if ($this->token === '') {
            throw new RuntimeException('TELEGRAM_BOT_TOKEN is not configured.');
        }

        return new TelegramBot(new Nutgram(
            $this->token,
            new Configuration(
                container: $this->container,
                cache: $this->cache,
                pollingAllowedUpdates: ['message'],
            ),
        ));
    }
}
