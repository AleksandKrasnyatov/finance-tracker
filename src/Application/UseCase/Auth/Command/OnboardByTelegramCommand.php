<?php

declare(strict_types=1);

namespace App\Application\UseCase\Auth\Command;

final readonly class OnboardByTelegramCommand
{
    public function __construct(
        public int $telegramId,
    ) {
    }
}
