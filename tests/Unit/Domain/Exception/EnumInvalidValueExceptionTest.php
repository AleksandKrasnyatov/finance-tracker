<?php

declare(strict_types=1);

namespace Test\Unit\Domain\Exception;

use App\Domain\Enum\TransactionType;
use App\Domain\Exception\EnumInvalidValueException;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EnumInvalidValueExceptionTest extends TestCase
{
    #[Test]
    public function givenExistingClassWhenExceptionIsCreatedThenEntityClassAndNameAreAccessible(): void
    {
        $exception = new EnumInvalidValueException(TransactionType::class);

        self::assertSame(TransactionType::class, $exception->entityClass);
        self::assertSame('TransactionType', $exception->getEntityName());
    }

    #[Test]
    public function givenNonExistingClassWhenExceptionIsCreatedThenInvalidArgumentExceptionIsExpected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        /** @phpstan-ignore-next-line */
        new EnumInvalidValueException('NotARealClass');
    }
}
