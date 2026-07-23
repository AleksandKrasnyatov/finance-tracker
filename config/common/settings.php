<?php

declare(strict_types=1);

use App\Application\Event\EventDispatcherInterface;
use App\Application\Event\Reminder\ReminderSent;
use App\Application\Fetcher\ReminderCandidatesFetcherInterface;
use App\Application\Gateway\NotifierInterface;
use App\Application\UseCase\Reminder\Listener\MarkReminderSentListener;
use App\Infrastructure\Event\SyncEventDispatcher;
use App\Infrastructure\Fetcher\ReminderCandidatesFetcher;
use App\Infrastructure\Gateway\TelegramNotifier;
use Psr\Container\ContainerInterface;

use function DI\autowire;

return [
    ReminderCandidatesFetcherInterface::class => autowire(ReminderCandidatesFetcher::class),
    NotifierInterface::class => autowire(TelegramNotifier::class),
    EventDispatcherInterface::class => static function (ContainerInterface $container): EventDispatcherInterface {
        /** @var MarkReminderSentListener $markReminderSent */
        $markReminderSent = $container->get(MarkReminderSentListener::class);

        return new SyncEventDispatcher([
            ReminderSent::class => [
                $markReminderSent,
            ],
        ]);
    },
];
