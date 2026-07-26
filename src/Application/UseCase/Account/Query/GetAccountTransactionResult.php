<?php

declare(strict_types=1);

namespace App\Application\UseCase\Account\Query;

use App\Application\Fetcher\Account\AccountTransaction;

final readonly class GetAccountTransactionResult
{
    public function __construct(
        public AccountTransaction $transaction,
    ) {
    }
}
