<?php

declare(strict_types=1);

namespace Test\Unit\Domain\ValueObject;

use App\Domain\ValueObject\TelegramId;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TelegramIdTest extends TestCase
{
    #[Test]
    public function givenNonZeroTelegramIdWhenTelegramIdIsCreatedThenValueMatches(): void
    {
        self::assertEquals(12, new TelegramId(12)->value);
        self::assertEquals(-124, new TelegramId(-124)->value);
    }

    #[Test]
    public function givenZeroTelegramIdWhenTelegramIdIsCreatedThenInvalidArgumentExceptionIsExpected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new TelegramId(0);
    }
}
