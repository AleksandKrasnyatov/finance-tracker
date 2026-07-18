<?php

declare(strict_types=1);

namespace App\Domain\Enum;

use App\Domain\Exception\EnumInvalidValueException;

enum TransactionType: string
{
    case Income = 'income';
    case Expense = 'expense';

    public static function fromName(string $typeName): self
    {
        return self::tryFrom($typeName) ?? throw new EnumInvalidValueException('Invalid transaction type');
    }

    public static function fromSign(string $sign): self
    {
        return match ($sign) {
            '+' => TransactionType::Income,
            '-' => TransactionType::Expense,
            default => throw new EnumInvalidValueException('Transaction sign must be + or -.')
        };
    }
}
