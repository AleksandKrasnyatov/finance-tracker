<?php

declare(strict_types=1);

use App\Application\Fetcher\ReminderCandidatesFetcherInterface;
use App\Application\Gateway\NotifierInterface;
use App\Infrastructure\Fetcher\ReminderCandidatesFetcher;
use App\Infrastructure\Gateway\TelegramNotifier;

use function DI\autowire;

return [
    ReminderCandidatesFetcherInterface::class => autowire(ReminderCandidatesFetcher::class),
    NotifierInterface::class => autowire(TelegramNotifier::class),
];
