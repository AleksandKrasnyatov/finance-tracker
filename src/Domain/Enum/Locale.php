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
}
