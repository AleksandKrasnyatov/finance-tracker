<?php

declare(strict_types=1);

namespace App\Domain\Enum;

enum Locale: string
{
    case Ru = 'ru';
    case En = 'en';

    public static function default(): self
    {
        return self::En;
    }

    public static function fromLanguageCode(?string $languageCode): self
    {
        if (empty($languageCode)) {
            return Locale::default();
        }

        $normalized = strtolower(str_replace('_', '-', $languageCode));
        $primary = explode('-', $normalized, 2)[0];

        return Locale::tryFrom($primary) ?? Locale::default();
    }
}
