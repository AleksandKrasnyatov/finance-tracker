<?php

declare(strict_types=1);

namespace App\Application\UseCase\Account\Query;

final readonly class GetAccountTransactionMonthsQuery
{
    public function __construct(
        public string $userId,
        public string $accountId,
        public int $page = 1,
        public int $perPage = 10,
    ) {
    }
}
