<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\User;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\TelegramId;
use DomainException;

interface UserRepositoryInterface
{
    /**
     * @throws DomainException
     */
    public function get(Id $id): User;

    /**
     * @throws DomainException
     */
    public function getByTelegramId(TelegramId $telegramId): User;

    public function hasByTelegramId(TelegramId $telegramId): bool;

    public function add(User $user): void;

    public function remove(User $user): void;
}
