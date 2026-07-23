<?php

declare(strict_types=1);

namespace App\Application\Gateway;

final readonly class Notification
{
    public const string REMINDER_NO_TRANSACTIONS_TODAY = 'reminder.no_transactions_today';

    /**
     * @param array<string, string|int|float> $parameters
     */
    public function __construct(
        public string $type,
        public array $parameters = [],
    ) {
    }
}
