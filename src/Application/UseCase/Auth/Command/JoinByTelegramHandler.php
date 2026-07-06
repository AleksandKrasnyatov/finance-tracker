<?php

declare(strict_types=1);

namespace App\Application\UseCase\Auth\Command;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\TelegramId;
use DateTimeImmutable;
use DomainException;

final readonly class JoinByTelegramHandler
{
    public function __construct(
        private UserRepositoryInterface $users,
    ) {
    }

    public function handle(JoinByTelegramCommand $command): void
    {
        $telegramId = new TelegramId($command->telegramId);
        if ($this->users->hasByTelegramId($telegramId)) {
            throw new DomainException('User with this telegram id already exists.');
        }

        $user = User::joinByTelegram($telegramId, new DateTimeImmutable());

        $this->users->save($user);
    }
}
