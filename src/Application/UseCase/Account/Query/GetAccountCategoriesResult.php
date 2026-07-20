<?php

declare(strict_types=1);

namespace App\Application\UseCase\Account\Query;

use App\Application\Fetcher\AccountCategory;

final readonly class GetAccountCategoriesResult
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
