<?php

declare(strict_types=1);

namespace App\Application\Fetcher;

use App\Domain\ValueObject\Id;

interface AccountCategoriesFetcherInterface
{
    public function fetch(Id $accountId): AccountCategories;
}
