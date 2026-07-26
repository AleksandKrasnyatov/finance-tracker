<?php

declare(strict_types=1);

namespace App\Application\UseCase\User\Command;

use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\Timezone;
use App\Infrastructure\Repository\Flusher;

final readonly class ChangeReminderTimezoneHandler
{
    public function __construct(
        private UserRepositoryInterface $users,
        private Flusher $flusher,
    ) {
    }

    public function handle(ChangeReminderTimezoneCommand $command): void
    {
        $user = $this->users->get(new Id($command->userId));
        $user->changeReminderTimezone(new Timezone($command->timezone));
        $this->flusher->flush();
    }
}
