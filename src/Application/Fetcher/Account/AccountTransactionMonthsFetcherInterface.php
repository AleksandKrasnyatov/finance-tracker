<?php

declare(strict_types=1);

namespace App\Application\Fetcher\Account;

use App\Domain\ValueObject\Id;

interface AccountTransactionMonthsFetcherInterface
{
    /**
     * @return list<AccountTransactionMonth>
     */
    public function fetch(Id $accountId, int $offset, int $limit): array;
}
