<?php

declare(strict_types=1);

namespace App\Application\Fetcher;

final readonly class AccountBalance
{
    public function __construct(
        public string $accountId,
        public int $year,
        public int $month,
        public int $balance,
        public int $incomes,
        public int $expenses,
        public string $currency,
    ) {
    }
}
