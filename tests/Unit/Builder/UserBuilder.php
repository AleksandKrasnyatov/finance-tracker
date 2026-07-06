<?php

declare(strict_types=1);

namespace Test\Unit\Builder;

use App\Domain\Entity\User;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\TelegramId;
use DateTimeImmutable;

final class UserBuilder
{
    private Id $id;

    private DateTimeImmutable $createdAt;
    private ?TelegramId $telegramId = null;

    public function __construct()
    {
        $this->id = Id::generate();
        $this->createdAt = new DateTimeImmutable();
    }

    public function withTelegramId(TelegramId $telegramId): self
    {
        $clone = clone $this;
        $clone->telegramId = $telegramId;
        return $clone;
    }

    public function build(): User
    {
        return new User(
            $this->id,
            $this->createdAt,
            $this->telegramId,
        );
    }
}
