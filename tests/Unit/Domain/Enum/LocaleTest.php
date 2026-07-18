<?php

declare(strict_types=1);

namespace Test\Unit\Domain\Enum;

use App\Domain\Enum\Locale;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class LocaleTest extends TestCase
{
    /**
     * @dataProvider languageCodes
     */
    #[Test]
    public function givenLanguageCodeWhenMakeLocaleThenExpectedLocaleIsCreated(
        ?string $languageCode,
        Locale $expected
    ): void {
        self::assertSame($expected, Locale::fromLanguageCode($languageCode));
    }

    /**
     * @return array<string, array{0: ?string, 1: Locale}>
     */
    public static function languageCodes(): array
    {
        return [
            'null' => [null, Locale::En],
            'empty' => ['', Locale::En],
            'ru' => ['ru', Locale::Ru],
            'ru_RU' => ['ru_RU', Locale::Ru],
            'ru-RU' => ['ru-RU', Locale::Ru],
            'en' => ['en', Locale::En],
            'en_US' => ['en_US', Locale::En],
            'en-GB' => ['en-GB', Locale::En],
            'unsupported' => ['de', Locale::En],
        ];
    }
}
