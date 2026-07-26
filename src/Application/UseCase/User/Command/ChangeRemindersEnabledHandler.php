<?php

declare(strict_types=1);

namespace App\Application\UseCase\User\Command;

use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Id;
use App\Infrastructure\Repository\Flusher;

final readonly class ChangeRemindersEnabledHandler
{
    public function __construct(
        private UserRepositoryInterface $users,
        private Flusher $flusher,
    ) {
    }

    public function handle(ChangeRemindersEnabledCommand $command): void
    {
        $user = $this->users->get(new Id($command->userId));

        if ($command->enabled) {
            $user->enableReminders();
        } else {
            $user->disableReminders();
        }

        $this->flusher->flush();
    }
}
