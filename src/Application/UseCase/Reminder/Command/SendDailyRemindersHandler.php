<?php

declare(strict_types=1);

namespace App\Application\UseCase\Reminder\Command;

use App\Application\Event\EventDispatcherInterface;
use App\Application\Event\Reminder\ReminderSent;
use App\Application\Fetcher\ReminderCandidatesFetcherInterface;
use App\Application\Gateway\Notification;
use App\Application\Gateway\NotifierInterface;
use DateTimeImmutable;

final readonly class SendDailyRemindersHandler
{
    public function __construct(
        private ReminderCandidatesFetcherInterface $candidates,
        private NotifierInterface $notifier,
        private EventDispatcherInterface $events,
    ) {
    }

    public function handle(DateTimeImmutable $now): void
    {
        foreach ($this->candidates->fetch($now) as $candidate) {
            $this->notifier->notify(
                $candidate->userId,
                new Notification(Notification::REMINDER_NO_TRANSACTIONS_TODAY),
            );

            $this->events->dispatch(new ReminderSent($candidate->userId, $now));
        }
    }
}
