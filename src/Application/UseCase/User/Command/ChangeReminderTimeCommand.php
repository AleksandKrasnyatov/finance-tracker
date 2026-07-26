<?php

declare(strict_types=1);

namespace App\Application\UseCase\User\Command;

final readonly class ChangeReminderTimeCommand
{
    public function __construct(
        public string $userId,
        public string $time,
    ) {
    }
}
