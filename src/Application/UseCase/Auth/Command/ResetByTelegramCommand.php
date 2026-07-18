<?php

declare(strict_types=1);

namespace App\Application\UseCase\Auth\Command;

final readonly class ResetByTelegramCommand
{
    public function __construct(
        public int $telegramId,
    ) {
    }
}
