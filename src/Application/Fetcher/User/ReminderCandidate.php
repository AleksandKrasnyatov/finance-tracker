<?php

declare(strict_types=1);

namespace App\Application\Fetcher\User;

use App\Domain\ValueObject\Id;

final readonly class ReminderCandidate
{
    public function __construct(
        public Id $userId,
    ) {
    }
}
