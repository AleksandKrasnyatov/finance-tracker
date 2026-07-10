<?php

declare(strict_types=1);

namespace App\Application\UseCase\Account\Category;

final readonly class ChangeCategoryNameCommand
{
    public function __construct(
        public string $userId,
        public string $accountId,
        public string $categoryId,
        public string $name,
    ) {
    }
}
