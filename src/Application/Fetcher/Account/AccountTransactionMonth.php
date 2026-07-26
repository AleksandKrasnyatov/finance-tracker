<?php

declare(strict_types=1);

namespace App\Application\Fetcher\Account;

final readonly class AccountTransactionMonth
{
    public function __construct(
        public string $year,
        public string $month,
    ) {
    }
}
