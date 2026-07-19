<?php

declare(strict_types=1);

namespace App\Application\Fetcher;

use App\Domain\ValueObject\Id;

interface AccountBalanceFetcherInterface
{
    public function fetchCurrentMonth(Id $accountId): AccountBalance;
}
