<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Enum\Locale;
use DateInvalidTimeZoneException;
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

    /**
     * @return list<self>
     */
    public static function common(): array
    {
        return [
            new self('Pacific/Midway'),
            new self('Pacific/Honolulu'),
            new self('America/Anchorage'),
            new self('America/Los_Angeles'),
            new self('America/Denver'),
            new self('America/Chicago'),
            new self('America/New_York'),
            new self('America/Halifax'),
            new self('America/Sao_Paulo'),
            new self('Atlantic/South_Georgia'),
            new self('Atlantic/Azores'),
            new self('UTC'),
            new self('Europe/London'),
            new self('Europe/Berlin'),
            new self('Europe/Helsinki'),
            new self('Europe/Moscow'),
            new self('Asia/Dubai'),
            new self('Asia/Karachi'),
            new self('Asia/Dhaka'),
            new self('Asia/Bangkok'),
            new self('Asia/Shanghai'),
            new self('Asia/Tokyo'),
            new self('Australia/Sydney'),
            new self('Pacific/Auckland'),
        ];
    }

    /**
     * @throws DateInvalidTimeZoneException
     */
    public function toDateTimeZone(): DateTimeZone
    {
        return new DateTimeZone($this->value);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
