<?php

declare(strict_types=1);

namespace App\Domain\Dto;

use App\Domain\Enum\TransactionType;
use Webmozart\Assert\Assert;

final readonly class CategoryDto
{
    public function __construct(
        public TransactionType $type,
        public string $name,
    ) {
        Assert::stringNotEmpty($name);
    }
}
