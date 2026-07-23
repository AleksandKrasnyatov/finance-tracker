<?php

declare(strict_types=1);

namespace App\Application\UseCase\Reminder\Command;

use App\Application\Fetcher\ReminderCandidatesFetcherInterface;
use App\Application\Gateway\Notification;
use App\Application\Gateway\NotifierInterface;

final readonly class SendDailyRemindersHandler
{
    public function __construct(
        private ReminderCandidatesFetcherInterface $candidates,
        private NotifierInterface $notifier,
    ) {
    }

    public function handle(): void
    {
        //todo додумать
        foreach ($this->candidates->fetch() as $candidate) {
            $this->notifier->notify(
                $candidate->userId,
                new Notification(Notification::REMINDER_NO_TRANSACTIONS_TODAY),
            );
        }
    }
}
