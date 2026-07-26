<?php

declare(strict_types=1);

namespace App\Application\Fetcher\Account;

use App\Domain\Enum\TransactionType;
use App\Domain\ValueObject\Id;

interface AccountTransactionsFetcherInterface
{
    /**
     * @return list<AccountTransaction>
     */
    public function fetch(
        Id $accountId,
        string $year,
        string $month,
        ?TransactionType $type = null,
    ): array;

    public function fetchOne(Id $accountId, Id $transactionId): ?AccountTransaction;
}
