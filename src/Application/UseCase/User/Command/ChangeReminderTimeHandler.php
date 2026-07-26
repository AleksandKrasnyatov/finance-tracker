<?php

declare(strict_types=1);

namespace App\Application\UseCase\User\Command;

use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\ReminderTime;
use App\Infrastructure\Repository\Flusher;

final readonly class ChangeReminderTimeHandler
{
    public function __construct(
        private UserRepositoryInterface $users,
        private Flusher $flusher,
    ) {
    }

    public function handle(ChangeReminderTimeCommand $command): void
    {
        $user = $this->users->get(new Id($command->userId));
        $user->changeReminderTime(new ReminderTime($command->time));
        $this->flusher->flush();
    }
}
