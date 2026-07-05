<?php

declare(strict_types=1);

namespace App\Domain\Entity;


use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\TelegramId;

final class User
{
    public function __construct(
        public Id $id,
        public ?TelegramId $telegramId,
    ) {
    }

    public static function joinByTelegram(TelegramId $telegramId): self
    {
        return new self(
            Id::generate(),
            $telegramId
        );
    }
}
