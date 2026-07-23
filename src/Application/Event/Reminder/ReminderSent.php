<?php

declare(strict_types=1);

namespace App\Application\Event\Reminder;

use App\Domain\ValueObject\Id;
use DateTimeImmutable;

final readonly class ReminderSent
{
    public function __construct(
        public Id $userId,
        public DateTimeImmutable $sentAt,
    ) {
    }
}
