<?php

declare(strict_types=1);

namespace Test\Unit\Infrastructure\Bot\Telegram\Formatter;

use App\Application\UseCase\Account\Query\GetAccountBalanceResult;
use App\Domain\Enum\Currency;
use App\Domain\Enum\Locale;
use App\Infrastructure\Bot\Telegram\Formatter\BalanceMessageFormatter;
use App\Infrastructure\Translation\SymfonyTranslator;
use App\Infrastructure\Translation\TranslationFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class BalanceMessageFormatterTest extends TestCase
{
    private BalanceMessageFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new BalanceMessageFormatter(
            new SymfonyTranslator(new TranslationFactory()->create()),
        );

        parent::setUp();
    }

    #[Test]
    public function givenAccountBalanceResultWhenFormatForMessageThenExpectedMessageIsComing(): void
    {
        $message = $this->formatter->format(
            new GetAccountBalanceResult(
                year: 2026,
                month: 7,
                balance: 11596,
                incomes: 12596,
                expenses: 1000,
                currency: Currency::USD->value,
            ),
            Locale::En,
        );

        self::assertSame(
            <<<'TEXT'
            📊 Balance for July

            📝 +11 596 $

            ➕ 12 596 $
            ➖ 1 000 $

            Remaining from income:
            💚💚💚💚💚💚💚💚💚🩸  92%

            TEXT,
            $message,
        );
    }

    #[Test]
    public function givenAccountBalanceResultWhenFormatForMessageWithRussianLocaleThenSeeRussianStringsAndCorrectData(): void
    {
        $message = $this->formatter->format(
            new GetAccountBalanceResult(
                year: 2026,
                month: 7,
                balance: -500,
                incomes: 0,
                expenses: 500,
                currency: Currency::RUB->value,
            ),
            Locale::Ru,
        );

        self::assertStringContainsString('Баланс за Июль', $message);
        self::assertStringContainsString('📝 −500 ₽', $message);
        self::assertStringContainsString('🩸🩸🩸🩸🩸🩸🩸🩸🩸🩸  0%', $message);
    }

    #[Test]
    public function ivenAccountBalanceResultWhenNoTransactionsThenShowsFullRemainingBar(): void
    {
        $message = $this->formatter->format(
            new GetAccountBalanceResult(
                year: 2026,
                month: 1,
                balance: 0,
                incomes: 0,
                expenses: 0,
                currency: Currency::USD->value,
            ),
            Locale::En,
        );

        self::assertStringContainsString('📝 +0 $', $message);
        self::assertStringContainsString('💚💚💚💚💚💚💚💚💚💚  100%', $message);
    }
}
