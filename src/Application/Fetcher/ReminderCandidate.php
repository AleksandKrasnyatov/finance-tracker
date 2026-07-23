<?php

declare(strict_types=1);

namespace App\Application\Fetcher;

use App\Domain\Enum\Locale;
use App\Domain\ValueObject\Id;

final readonly class ReminderCandidate
{
    public function __construct(
        public Id $userId,
        public Locale $locale,
    ) {
    }
}
