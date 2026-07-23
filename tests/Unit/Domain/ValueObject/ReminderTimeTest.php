<?php

declare(strict_types=1);

namespace Test\Unit\Domain\ValueObject;

use App\Domain\ValueObject\ReminderTime;
use DomainException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ReminderTimeTest extends TestCase
{
    #[Test]
    public function givenValidTimeWhenReminderTimeIsCreatedThenValueMatches(): void
    {
        $time = new ReminderTime($value = '21:00');

        self::assertSame($value, $time->value);
    }

    #[Test]
    public function givenValidTimeWhenCastToStringThenStringMatchesValue(): void
    {
        $time = new ReminderTime('09:05');

        self::assertSame('09:05', (string) $time);
    }

    #[Test]
    public function givenNothingWhenDefaultIsCreatedThenValueIs2100(): void
    {
        self::assertSame('21:00', ReminderTime::default()->value);
    }

    /**
     * @dataProvider validTimes
     */
    #[Test]
    public function givenBoundaryValidTimeWhenReminderTimeIsCreatedThenValueMatches(string $value): void
    {
        self::assertSame($value, new ReminderTime($value)->value);
    }

    /**
     * @return list<string[]>
     */
    public static function validTimes(): array
    {
        return [
            ['00:00'],
            ['09:05'],
            ['19:59'],
            ['20:00'],
            ['23:59'],
        ];
    }

    /**
     * @dataProvider invalidTimes
     */
    #[Test]
    public function givenInvalidTimeWhenReminderTimeIsCreatedThenDomainExceptionIsExpected(string $invalidTime): void
    {
        $this->expectException(DomainException::class);
        new ReminderTime($invalidTime);
    }

    /**
     * @return list<string[]>
     */
    public static function invalidTimes(): array
    {
        return [
            [''],
            ['9:00'],
            ['24:00'],
            ['21:60'],
            ['21:00:00'],
            ['21-00'],
            ['ab:cd'],
        ];
    }
}
