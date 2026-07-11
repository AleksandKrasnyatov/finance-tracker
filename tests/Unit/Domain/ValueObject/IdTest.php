<?php

declare(strict_types=1);

namespace Test\Unit\Domain\ValueObject;

use App\Domain\ValueObject\Id;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class IdTest extends TestCase
{
    #[Test]
    public function givenValidUuidWhenIdIsCreatedThenValueMatches(): void
    {
        $id = new Id($value = Uuid::uuid4()->toString());

        self::assertEquals($value, $id->value);
    }

    #[Test]
    public function givenUppercaseUuidWhenIdIsCreatedThenValueIsLowercased(): void
    {
        $value = Uuid::uuid4()->toString();
        $id = new Id(mb_strtoupper($value));

        self::assertEquals($value, $id->value);
    }

    #[Test]
    public function givenNothingWhenIdIsGeneratedThenValueIsNotEmpty(): void
    {
        $id = Id::generate();

        self::assertNotEmpty($id->value);
    }

    #[Test]
    public function givenInvalidUuidWhenIdIsCreatedThenInvalidArgumentExceptionIsExpected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Id('12345');
    }

    #[Test]
    public function givenEmptyStringWhenIdIsCreatedThenInvalidArgumentExceptionIsExpected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Id('');
    }
}
