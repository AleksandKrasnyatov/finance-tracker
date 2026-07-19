<?php

declare(strict_types=1);

namespace Test\Functional\Infrastructure\Bot\Telegram\Handler;

use App\Application\UseCase\Account\Query\GetAccountBalanceResult;
use App\Domain\Enum\Currency;
use App\Domain\Enum\Locale;
use App\Infrastructure\Bot\Telegram\Formatter\BalanceMessageFormatter;
use DateTimeImmutable;
use Test\Support\Fixture\OnboardedTelegramUserFixture;
use Test\Support\Fixture\OnboardedTelegramUserWithBalanceFixture;
use Test\Support\FunctionalTester;
use Test\Support\TelegramBotTester;

final class BalanceHandlerCest
{
    public function givenTransactionsInCurrentMonthWhenBalanceCommandThenFormattedMessageIsSent(
        FunctionalTester $I,
    ): void {
        $I->loadFixtures(OnboardedTelegramUserWithBalanceFixture::class);
        $bot = TelegramBotTester::configure($I, OnboardedTelegramUserWithBalanceFixture::TELEGRAM_ID);

        /** @var BalanceMessageFormatter $formatter */
        $formatter = $I->grabService(BalanceMessageFormatter::class);
        $now = new DateTimeImmutable();

        $expected = $formatter->format(
            new GetAccountBalanceResult(
                (int)$now->format('Y'),
                (int)$now->format('n'),
                OnboardedTelegramUserWithBalanceFixture::BALANCE,
                OnboardedTelegramUserWithBalanceFixture::INCOMES,
                OnboardedTelegramUserWithBalanceFixture::EXPENSES,
                Currency::RUB->value,
            ),
            Locale::En,
        );

        $bot
            ->hearText('/balance')
            ->reply()
            ->assertReplyText($expected);
    }

    public function givenNoTransactionsWhenBalanceCommandThenZeroBalanceMessageIsSent(FunctionalTester $I): void
    {
        $I->loadFixtures(OnboardedTelegramUserFixture::class);
        $bot = TelegramBotTester::configure($I, OnboardedTelegramUserFixture::TELEGRAM_ID);

        /** @var BalanceMessageFormatter $formatter */
        $formatter = $I->grabService(BalanceMessageFormatter::class);
        $now = new DateTimeImmutable();

        $expected = $formatter->format(
            new GetAccountBalanceResult(
                (int)$now->format('Y'),
                (int)$now->format('n'),
                0,
                0,
                0,
                Currency::RUB->value,
            ),
            Locale::En,
        );

        $bot
            ->hearText('/balance')
            ->reply()
            ->assertReplyText($expected);
    }
}
