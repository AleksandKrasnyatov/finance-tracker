<?php

declare(strict_types=1);

namespace App\Application\UseCase\Account\Query;

use App\Application\Fetcher\Account\AccountTransactionMonth;

final readonly class GetAccountTransactionMonthsResult
{
    /**
     * @param list<AccountTransactionMonth> $months
     */
    public function __construct(
        public array $months,
        public int $page,
        public bool $hasPrev,
        public bool $hasNext,
    ) {
    }
}
