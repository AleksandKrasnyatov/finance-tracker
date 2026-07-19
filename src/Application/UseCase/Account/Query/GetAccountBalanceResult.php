<?php

declare(strict_types=1);

namespace App\Application\UseCase\Account\Query;

final readonly class GetAccountBalanceResult
{
    public function __construct(
        public int $year,
        public int $month,
        public int $balance,
        public int $incomes,
        public int $expenses,
        public string $currency,
    ) {
    }
}
