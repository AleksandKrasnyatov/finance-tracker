<?php

declare(strict_types=1);

namespace App\Application\UseCase\Reminder\Listener;

use App\Application\Event\Reminder\ReminderSent;
use App\Domain\Repository\UserRepositoryInterface;
use App\Infrastructure\Repository\Flusher;

final readonly class MarkReminderSentListener
{
    public function __construct(
        private UserRepositoryInterface $users,
        private Flusher $flusher,
    ) {
    }

    public function __invoke(ReminderSent $event): void
    {
        $user = $this->users->get($event->userId);
        $user->markReminderSent($event->sentAt);
        $this->flusher->flush();
    }
}
