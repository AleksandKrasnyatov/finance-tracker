<?php

declare(strict_types=1);

namespace App\Application\UseCase\Account\Query;

final readonly class GetAccountTransactionsQuery
{
    public function __construct(
        public string $userId,
        public string $accountId,
        public string $year,
        public string $month,
        public ?string $filter = null,
    ) {
    }
}
