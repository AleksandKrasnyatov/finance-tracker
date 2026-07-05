<?php

declare(strict_types=1);

namespace Test\Unit\Domain\ValueObject;

use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\TelegramId;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class TelegramIdTest extends TestCase
{
    public function testSuccess(): void
    {
        self::assertEquals(12, new TelegramId(12)->value);
        self::assertEquals(-124, new TelegramId(-124)->value);
    }

    public function testIncorrect(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new TelegramId(0);
    }
}
