<?php

declare(strict_types=1);

namespace App\Domain\Enum;

enum Locale: string
{
    case Ru = 'ru';
    case En = 'en';

    public function label(): string
    {
        return match ($this) {
            self::Ru => 'Русский',
            self::En => 'English',
        };
    }

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

    /**
     * @return array<string, string>
     */
    public static function list(): array
    {
        $list = [];
        foreach (Locale::cases() as $locale) {
            $list[$locale->value] = $locale->label();
        }

        return $list;
    }
}
