<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use DomainException;

final readonly class ReminderTime
{
    public function __construct(
        public string $value,
    ) {
        if (preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $this->value) !== 1) {
            throw new DomainException(sprintf('Invalid reminder time "%s", expected HH:MM.', $this->value));
        }
    }

    public static function default(): self
    {
        return new self('21:00');
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
