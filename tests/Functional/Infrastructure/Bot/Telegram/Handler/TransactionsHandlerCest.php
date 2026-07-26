<?php

declare(strict_types=1);

namespace Test\Functional\Infrastructure\Bot\Telegram\Handler;

use App\Application\Gateway\TranslatorInterface;
use App\Infrastructure\Bot\Telegram\Handler\Transaction\TransactionCallback;
use DateTimeImmutable;
use SergiX44\Nutgram\Testing\FakeNutgram;
use Test\Support\Fixture\OnboardedTelegramUserFixture;
use Test\Support\Fixture\OnboardedTelegramUserWithTransactionMonthsFixture;
use Test\Support\FunctionalTester;
use Test\Support\TelegramBotTester;

final class TransactionsHandlerCest
{
    private FakeNutgram $bot;
    private TranslatorInterface $translator;

    public function _before(FunctionalTester $I): void
    {
        $this->bot = TelegramBotTester::configure($I, OnboardedTelegramUserFixture::TELEGRAM_ID);
        /** @var TranslatorInterface $translator */
        $translator = $I->grabService(TranslatorInterface::class);
        $this->translator = $translator;
    }

    public function givenTransactionsCommandWhenOpenedThenJournalScreenIsShown(FunctionalTester $I): void
    {
        $I->loadFixtures(OnboardedTelegramUserFixture::class);
        $currentMonth = new DateTimeImmutable()->format('n');

        $this->bot
            ->hearText('/transactions')
            ->reply()
            ->assertReplyMessage([
                'text' => $this->translator->trans('bot.transactions.title'),
                'parse_mode' => 'HTML',
                'reply_markup' => [
                    'inline_keyboard' => [
                        [
                            ['text' => $this->translator->trans('bot.month.' . $currentMonth)],
                        ],
                        [
                            ['text' => '🟠 ' . $this->translator->trans('bot.transactions.filter.all')],
                            ['text' => '⚪️ ' . $this->translator->trans('bot.transactions.filter.expense')],
                            ['text' => '⚪️ ' . $this->translator->trans('bot.transactions.filter.income')],
                        ],
                        [
                            ['text' => $this->translator->trans('bot.transactions.back')],
                        ],
                    ],
                ],
            ]);
    }

    public function givenMonthsInDifferentYearsWhenOpensPeriodThenOnlyThoseMonthsAreShown(FunctionalTester $I): void
    {
        $I->loadFixtures(OnboardedTelegramUserWithTransactionMonthsFixture::class);
        $now = new DateTimeImmutable();

        $this->bot->hearText('/transactions')->reply();
        $this->bot
            ->hearCallbackQueryData(
                TransactionCallback::data(
                    TransactionCallback::MONTH,
                    $now->format('Y'),
                    $now->format('n'),
                    TransactionCallback::FILTER_ALL,
                    '1',
                )
            )
            ->reply()
            ->assertReply('editMessageText', [
                'text' => $this->translator->trans('bot.transactions.chooseMonth'),
                'reply_markup' => [
                    'inline_keyboard' => [
                        [['text' => 'July 2026']],
                        [['text' => 'June 2024']],
                        [['text' => $this->translator->trans('bot.transactions.back')]],
                    ],
                ],
            ], 1);
    }
}
