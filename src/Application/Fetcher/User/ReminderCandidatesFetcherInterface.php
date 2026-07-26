<?php

declare(strict_types=1);

namespace App\Application\Fetcher\User;

use DateTimeImmutable;

interface ReminderCandidatesFetcherInterface
{
    /**
     * @return list<ReminderCandidate>
     */
    public function fetch(DateTimeImmutable $now): array;
}
