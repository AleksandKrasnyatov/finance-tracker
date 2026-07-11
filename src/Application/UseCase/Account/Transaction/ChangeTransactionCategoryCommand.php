<?php

declare(strict_types=1);

namespace App\Application\UseCase\Account\Transaction;

final readonly class ChangeTransactionCategoryCommand
{
    public function __construct(
        public string $userId,
        public string $accountId,
        public string $transactionId,
        public string $categoryId,
    ) {
    }
}
