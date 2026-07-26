<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use DomainException;

final readonly class ReminderTime
{
    public string $value;

    public function __construct(string $value)
    {
        if (preg_match('/^(0?[0-9]|1[0-9]|2[0-3]):([0-5]\d)$/', $value, $matches) !== 1) {
            throw new DomainException(sprintf('Invalid reminder time "%s", expected HH:MM.', $value));
        }

        $this->value = sprintf('%02d:%s', (int)$matches[1], $matches[2]);
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
