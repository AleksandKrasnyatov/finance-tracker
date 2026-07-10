<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

final readonly class Id
{
    /**
     * @var non-empty-string
     */
    public string $value;

    public function __construct(string $value)
    {
        Assert::notEmpty($value);
        Assert::uuid($value);
        $this->value = mb_strtolower($value);
    }

    public static function generate(): self
    {
        return new self(Uuid::uuid4()->toString());
    }

    public function equals(Id $id): bool
    {
        return $this->value === $id->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
