<?php

declare(strict_types=1);

namespace App\Application\UseCase\User\Command;

final readonly class ChangeUserLocaleCommand
{
    public function __construct(
        public string $userId,
        public string $locale,
    ) {
    }
}
