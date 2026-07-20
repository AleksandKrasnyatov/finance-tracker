<?php

declare(strict_types=1);

namespace App\Application\UseCase\Account\Query;

use App\Application\Fetcher\AccountCategory;

final readonly class GetAccountCategoryResult
{
    public function __construct(
        public AccountCategory $category,
    ) {
    }
}
