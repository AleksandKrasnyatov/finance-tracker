<?php

declare(strict_types=1);

namespace App\Infrastructure\Translation;

use App\Domain\Enum\Locale;

final class LocaleMapper
{
    public function fromTelegramLanguageCode(?string $languageCode): Locale
    {
        if (empty($languageCode)) {
            return Locale::default();
        }

        $normalized = strtolower(str_replace('_', '-', $languageCode));
        $primary = explode('-', $normalized, 2)[0];

        return Locale::tryFrom($primary) ?? Locale::default();
    }
}
