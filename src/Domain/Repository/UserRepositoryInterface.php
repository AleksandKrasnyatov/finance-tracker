<?php

namespace App\Domain\Repository;

use App\Domain\Entity\User;
use App\Domain\ValueObject\TelegramId;

interface UserRepositoryInterface
{
    public function hasByTelegramId(TelegramId $telegramId): bool;
    public function save(User $user): void;
}
