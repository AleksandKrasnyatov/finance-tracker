<?php

declare(strict_types=1);

namespace App\Application\UseCase\Account\Query;

final readonly class GetAccountCategoryQuery
{
    public function __construct(
        public string $userId,
        public string $accountId,
        public string $categoryId,
    ) {
    }
}
