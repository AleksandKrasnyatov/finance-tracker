<?php

declare(strict_types=1);

namespace App\Application\Fetcher\Account;

use App\Domain\Enum\Currency;
use App\Domain\Enum\TransactionType;
use DateTimeImmutable;

final readonly class AccountTransaction
{
    public function __construct(
        public string $id,
        public TransactionType $type,
        public string $amount,
        public Currency $currency,
        public string $categoryId,
        public string $categoryName,
        public DateTimeImmutable $date,
    ) {
    }
}
