<?php

declare(strict_types=1);

namespace App\Application\UseCase\Reminder\Command;

use App\Application\Fetcher\ReminderCandidatesFetcherInterface;
use App\Application\Gateway\Notification;
use App\Application\Gateway\NotifierInterface;
use App\Infrastructure\Repository\Flusher;
use App\Infrastructure\Repository\UserRepository;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class SendDailyRemindersHandler
{
    public function __construct(
        private ReminderCandidatesFetcherInterface $candidates,
        private NotifierInterface $notifier,
        private UserRepository $users,
        private LoggerInterface $logger,
        private Flusher $flusher,
    ) {
    }

    public function handle(DateTimeImmutable $now): void
    {
        foreach ($this->candidates->fetch($now) as $candidate) {
            try {
                $user = $this->users->get($candidate->userId);
                $this->notifier->notify($user->id, new Notification(Notification::REMINDER_NO_TRANSACTIONS_TODAY));
                $user->markReminderSent($now);
            } catch (Throwable $exception) {
                $this->logger->error('Failed to send reminder', [
                    'userId' => $candidate->userId,
                    'error' => $exception->getTraceAsString(),
                ]);
            }
        }

        $this->flusher->flush();
    }
}
