<?php

declare(strict_types=1);

namespace App\Infrastructure\Event;

use App\Application\Event\EventDispatcherInterface;

final readonly class SyncEventDispatcher implements EventDispatcherInterface
{
    /**
     * @param array<class-string, list<callable(object): void>> $listeners
     */
    public function __construct(
        private array $listeners = [],
    ) {
    }

    public function dispatch(object $event): void
    {
        foreach ($this->listeners[$event::class] ?? [] as $listener) {
            $listener($event);
        }
    }
}
