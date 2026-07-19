<?php

declare(strict_types=1);

namespace App\Application\UseCase\Account\Command\Category;

final readonly class CreateCategoryCommand
{
    public function __construct(
        public string $userId,
        public string $accountId,
        public string $type,
        public string $name,
    ) {
    }
}
