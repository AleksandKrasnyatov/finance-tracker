<?php

declare(strict_types=1);

namespace App\Domain\Enum;

use DomainException;

enum TransactionType: string
{
    case Income = 'income';
    case Expense = 'expense';

    public static function makeFrom(string $typeName): self
    {
        return self::tryFrom($typeName) ?? throw new DomainException('Invalid transaction type');
    }
}
