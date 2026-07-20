<?php

declare(strict_types=1);

namespace App\Application\Fetcher;

final readonly class AccountCategories
{
    /**
     * @param list<AccountCategory> $incomes
     * @param list<AccountCategory> $expenses
     */
    public function __construct(
        public array $incomes,
        public array $expenses,
    ) {
    }
}
