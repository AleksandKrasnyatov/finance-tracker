<?php

declare(strict_types=1);

namespace App\Domain\Enum;

enum Currency: string
{
    case RUB = 'rub';
    case USD = 'usd';
    case EUR = 'eur';

    public function symbol(): string
    {
        return match ($this) {
            Currency::RUB => '₽',
            Currency::USD => '$',
            Currency::EUR => '€',
        };
    }
}
