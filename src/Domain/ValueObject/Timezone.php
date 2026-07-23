<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Enum\Locale;
use DateTimeZone;
use DomainException;
use Exception;

final readonly class Timezone
{
    public function __construct(
        public string $value,
    ) {
        try {
            new DateTimeZone($this->value);
        } catch (Exception) {
            throw new DomainException(sprintf('Invalid IANA timezone "%s".', $this->value));
        }
    }

    public static function defaultForLocale(?Locale $locale = null): self
    {
        return match ($locale) {
            Locale::Ru => new self('Europe/Moscow'),
            default => new self('UTC'),
        };
    }

    public function toDateTimeZone(): DateTimeZone
    {
        return new DateTimeZone($this->value);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
