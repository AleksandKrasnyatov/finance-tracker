<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram;

use Psr\Container\ContainerInterface;
use SergiX44\Container\Exception\NotFoundException;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Telegram\Types\BaseType;

/**
 * Нужен чтобы пофиксить баг с ключом при реализации Conversations.
 */
final readonly class NutgramContainer implements ContainerInterface
{
    public function __construct(
        private ContainerInterface $container,
    ) {
    }

    public function get(string $id): mixed
    {
        if (!$this->has($id)) {
            throw new NotFoundException('Not found: ' . $id);
        }

        return $this->container->get($id);
    }

    public function has(string $id): bool
    {
        if (
            is_subclass_of($id, BaseType::class, true)
            || is_subclass_of($id, Conversation::class, true)
            || str_starts_with($id, 'SergiX44\\')
        ) {
            return false;
        }

        return $this->container->has($id);
    }
}
