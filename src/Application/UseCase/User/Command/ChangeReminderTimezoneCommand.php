<?php

declare(strict_types=1);

namespace App\Application\UseCase\User\Command;

final readonly class ChangeReminderTimezoneCommand
{
    public function __construct(
        public string $userId,
        public string $timezone,
    ) {
    }
}
