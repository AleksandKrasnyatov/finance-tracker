<?php

declare(strict_types=1);

namespace App\Application\Fetcher\Account;

use App\Domain\Entity\Account;
use App\Domain\ValueObject\Id;

interface AccountCategoriesFetcherInterface
{
    public function fetch(Id $accountId): AccountCategories;

    public function fetchOne(Account $account, Id $categoryId): ?AccountCategory;
}
