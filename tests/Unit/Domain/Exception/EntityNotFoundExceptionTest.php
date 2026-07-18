<?php

declare(strict_types=1);

namespace Test\Unit\Domain\Exception;

use App\Domain\Entity\Category;
use App\Domain\Exception\EntityNotFoundException;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EntityNotFoundExceptionTest extends TestCase
{
    #[Test]
    public function givenExistingClassWhenExceptionIsCreatedThenEntityClassAndNameAreAccessible(): void
    {
        $exception = new EntityNotFoundException(Category::class);

        self::assertSame(Category::class, $exception->entityClass);
        self::assertSame('Category', $exception->getEntityName());
    }

    #[Test]
    public function givenNonExistingClassWhenExceptionIsCreatedThenInvalidArgumentExceptionIsExpected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        /** @phpstan-ignore-next-line */
        new EntityNotFoundException('NotARealClass');
    }
}
