<?php

declare(strict_types=1);

namespace App\Application\UseCase\Account\Query;

use App\Application\Fetcher\Account\AccountTransaction;

final readonly class GetAccountTransactionsResult
{
    /**
     * @param list<AccountTransaction> $transactions
     */
    public function __construct(
        public array $transactions,
    ) {
    }
}
