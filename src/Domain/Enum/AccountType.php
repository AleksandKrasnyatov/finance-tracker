<?php

declare(strict_types=1);

namespace App\Domain\Enum;

enum AccountType: string
{
    case Personal = 'personal';
    case Joint = 'joint';
}
