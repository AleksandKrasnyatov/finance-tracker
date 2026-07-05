<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use Webmozart\Assert\Assert;

final readonly class TelegramId
{
    public function __construct(
        public int $value,
    ) {
        Assert::notEq($this->value, 0);
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
