<?php

declare(strict_types=1);

namespace App\Application\UseCase\Account\Transaction;

final readonly class AddTransactionCommand
{
    public function __construct(
        public string $userId,
        public string $accountId,
        public string $type,
        public string $amount,
        public string $category,
        public string $description = '',
        public ?string $date = null,
    ) {
    }
}
