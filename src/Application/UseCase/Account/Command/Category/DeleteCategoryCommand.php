<?php

declare(strict_types=1);

namespace App\Application\UseCase\Account\Command\Category;

final readonly class DeleteCategoryCommand
{
    public function __construct(
        public string $userId,
        public string $accountId,
        public string $categoryId,
    ) {
    }
}
