<?php

declare(strict_types=1);

namespace App\Application\UseCase\Auth\Command;

use App\Domain\Repository\AccountRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\TelegramId;
use App\Infrastructure\Repository\Flusher;

final readonly class ResetByTelegramHandler
{
    public function __construct(
        private UserRepositoryInterface $users,
        private AccountRepositoryInterface $accounts,
        private Flusher $flusher,
    ) {
    }

    public function handle(ResetByTelegramCommand $command): void
    {
        $telegramId = new TelegramId($command->telegramId);
        if (!$this->users->hasByTelegramId($telegramId)) {
            return;
        }

        $user = $this->users->getByTelegramId($telegramId);

        foreach ($user->getAccounts() as $account) {
            $this->accounts->remove($account);
        }

        $this->users->remove($user);
        $this->flusher->flush();
    }
}
