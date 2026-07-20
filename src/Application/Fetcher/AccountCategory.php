<?php

declare(strict_types=1);

namespace App\Application\Fetcher;

use App\Domain\Enum\TransactionType;
use App\Domain\ValueObject\Id;
use Webmozart\Assert\Assert;

final readonly class AccountCategory
{
    public function __construct(
        public Id $id,
        public string $name,
        public TransactionType $type,
    ) {
        Assert::notEmpty($name);
    }
}
