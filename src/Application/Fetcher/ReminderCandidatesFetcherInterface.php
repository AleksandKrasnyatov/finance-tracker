<?php

declare(strict_types=1);

namespace App\Application\Fetcher;

interface ReminderCandidatesFetcherInterface
{
    /**
     * @return list<ReminderCandidate>
     */
    public function fetch(): array;
}
