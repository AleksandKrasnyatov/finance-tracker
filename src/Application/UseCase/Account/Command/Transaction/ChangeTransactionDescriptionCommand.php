<?php

declare(strict_types=1);

namespace App\Application\UseCase\Account\Command\Transaction;

final readonly class ChangeTransactionDescriptionCommand
{
    public function __construct(
        public string $userId,
        public string $accountId,
        public string $transactionId,
        public string $description,
    ) {
    }
}
