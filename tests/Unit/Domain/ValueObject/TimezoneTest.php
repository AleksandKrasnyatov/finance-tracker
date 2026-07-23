<?php

declare(strict_types=1);

namespace Test\Unit\Domain\ValueObject;

use App\Domain\Enum\Locale;
use App\Domain\ValueObject\Timezone;
use DateTimeZone;
use DomainException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TimezoneTest extends TestCase
{
    #[Test]
    public function givenValidTimezoneWhenTimezoneIsCreatedThenValueMatches(): void
    {
        $timezone = new Timezone($value = 'Europe/Moscow');

        self::assertSame($value, $timezone->value);
    }

    #[Test]
    public function givenValidTimezoneWhenCastToStringThenStringMatchesValue(): void
    {
        $timezone = new Timezone('UTC');

        self::assertSame('UTC', (string) $timezone);
    }

    #[Test]
    public function givenValidTimezoneWhenConvertedToDateTimeZoneThenNameMatches(): void
    {
        $timezone = new Timezone('Europe/Moscow');

        self::assertInstanceOf(DateTimeZone::class, $timezone->toDateTimeZone());
        self::assertSame('Europe/Moscow', $timezone->toDateTimeZone()->getName());
    }

    #[Test]
    public function givenDifferentLocalesWhenDefaultForLocaleThenSeeExpectedTimeZone(): void
    {
        self::assertSame('Europe/Moscow', Timezone::defaultForLocale(Locale::Ru)->value);
        self::assertSame('UTC', Timezone::defaultForLocale()->value);
    }

    /**
     * @dataProvider invalidTimezones
     */
    #[Test]
    public function givenInvalidTimezoneWhenTimezoneIsCreatedThenDomainExceptionIsExpected(string $invalidTimezone): void
    {
        $this->expectException(DomainException::class);
        new Timezone($invalidTimezone);
    }

    /**
     * @return list<string[]>
     */
    public static function invalidTimezones(): array
    {
        return [
            [''],
            ['Not/AZone'],
            ['Moscow'],
        ];
    }
}
