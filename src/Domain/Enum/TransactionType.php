<?php

declare(strict_types=1);

namespace App\Domain\Enum;

enum TransactionType: string
{
    case Income = 'income';
    case Expense = 'expense';
}
