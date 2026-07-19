<?php

declare(strict_types=1);

namespace App\Application\UseCase\Account\Command;

final readonly class CreateUserAccountCommand
{
    public function __construct(
        public string $userId,
        public ?string $name = null,
        public string $type = '',
    ) {
    }
}
