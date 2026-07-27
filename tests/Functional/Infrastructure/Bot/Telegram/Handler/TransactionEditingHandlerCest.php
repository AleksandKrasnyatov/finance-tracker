<?php

declare(strict_types=1);

namespace Test\Functional\Infrastructure\Bot\Telegram\Handler;

use App\Application\Gateway\TranslatorInterface;
use App\Domain\Entity\Transaction;
use App\Infrastructure\Bot\Telegram\CallbackData;
use App\Infrastructure\Bot\Telegram\Handler\Transaction\TransactionCallback;
use SergiX44\Nutgram\Testing\FakeNutgram;
use Test\Support\Fixture\OnboardedTelegramUserFixture;
use Test\Support\Fixture\OnboardedTelegramUserWithTransactionMonthsFixture;
use Test\Support\FunctionalTester;
use Test\Support\TelegramBotTester;

final class TransactionEditingHandlerCest
{
    private FakeNutgram $bot;
    private TranslatorInterface $translator;
    private Transaction $transaction;

    public function _before(FunctionalTester $I): void
    {
        $this->bot = TelegramBotTester::configure($I, OnboardedTelegramUserFixture::TELEGRAM_ID);
        /** @var TranslatorInterface $translator */
        $translator = $I->grabService(TranslatorInterface::class);
        $this->translator = $translator;
        $I->loadFixtures(OnboardedTelegramUserWithTransactionMonthsFixture::class);
        $this->transaction = $I->grabEntityFromRepository(Transaction::class, [
            'money.amount' => '450',
        ]);
    }

    public function givenTransactionWhenOpensEditingThenCanSeeCorrectMarkup(FunctionalTester $I): void
    {
        $transactionId = $this->transaction->id->value;

        $this->bot->willStartConversation()
            ->hearText('/transactions')
            ->reply();

        $this->bot
            ->hearCallbackQueryData(CallbackData::data(TransactionCallback::VIEW, $transactionId))
            ->reply()
            ->assertReply('editMessageText', [
                'text' => $this->translator->trans('bot.transactions.detail'),
                'reply_markup' => [
                    'inline_keyboard' => [
                        [['text' => '450 ₽']],
                        [['text' => 'groceries']],
                        [['text' => '2026-07-26']],
                        [['text' => $this->translator->trans('bot.transactions.descriptionPlaceholder')]],
                        [['text' => '🗑 Delete']],
                        [['text' => '← Back']],
                    ],
                ],
            ], 1);
    }

    public function givenTransactionWhenEditAmountThenAmountSuccessfullyChanged(FunctionalTester $I): void
    {
        $transactionId = $this->transaction->id->value;

        $this->bot->willStartConversation()
            ->hearText('/transactions')
            ->reply();

        $this->bot
            ->hearCallbackQueryData(CallbackData::data(TransactionCallback::VIEW, $transactionId))
            ->reply();

        $this->bot
            ->hearCallbackQueryData(CallbackData::data(TransactionCallback::MONEY, $transactionId))
            ->reply()
            ->assertReply('sendMessage', [
                'text' => $this->translator->trans('bot.transactions.enterAmount'),
            ], 1)
            ->assertActiveConversation(
                OnboardedTelegramUserWithTransactionMonthsFixture::TELEGRAM_ID,
                OnboardedTelegramUserWithTransactionMonthsFixture::TELEGRAM_ID,
            );

        $this->bot
            ->hearText('500')
            ->reply()
            ->assertReplyText($this->translator->trans('bot.transactions.amountChanged', ['%amount%' => '500']));

        $I->seeInRepository(Transaction::class, [
            'id' => $transactionId,
            'money.amount' => '500.00',
        ]);
    }

    public function givenTransactionWhenEditDateThenDateSuccessfullyChanged(FunctionalTester $I): void
    {
        $transactionId = $this->transaction->id->value;

        $this->bot->willStartConversation()
            ->hearText('/transactions')
            ->reply();

        $this->bot
            ->hearCallbackQueryData(CallbackData::data(TransactionCallback::VIEW, $transactionId))
            ->reply();

        $this->bot
            ->hearCallbackQueryData(CallbackData::data(TransactionCallback::DATE, $transactionId))
            ->reply()
            ->assertReply('sendMessage', [
                'text' => $this->translator->trans('bot.transactions.enterDate'),
            ], 1)
            ->assertActiveConversation(
                OnboardedTelegramUserWithTransactionMonthsFixture::TELEGRAM_ID,
                OnboardedTelegramUserWithTransactionMonthsFixture::TELEGRAM_ID,
            );

        $this->bot
            ->hearText('25.10.2026')
            ->reply()
            ->assertReplyText($this->translator->trans('bot.transactions.dateChanged', ['%date%' => '25.10.2026']));


        $I->seeInRepository(Transaction::class, [
            'id' => $transactionId,
            'date' => '2026-10-25',
        ]);
    }

    public function givenTransactionWhenEditDescriptionThenDescriptionSuccessfullyChanged(FunctionalTester $I): void
    {
        $transactionId = $this->transaction->id->value;

        $this->bot->willStartConversation()
            ->hearText('/transactions')
            ->reply();

        $this->bot
            ->hearCallbackQueryData(CallbackData::data(TransactionCallback::VIEW, $transactionId))
            ->reply();

        $this->bot
            ->hearCallbackQueryData(CallbackData::data(TransactionCallback::DESCRIPTION, $transactionId))
            ->reply()
            ->assertReply('sendMessage', [
                'text' => $this->translator->trans('bot.transactions.enterDescription'),
            ], 1)
            ->assertActiveConversation(
                OnboardedTelegramUserWithTransactionMonthsFixture::TELEGRAM_ID,
                OnboardedTelegramUserWithTransactionMonthsFixture::TELEGRAM_ID,
            );

        $this->bot
            ->hearText($newDescription = 'new transaction description')
            ->reply()
            ->assertReplyText($this->translator->trans('bot.transactions.descriptionChanged'));


        $I->seeInRepository(Transaction::class, [
            'id' => $transactionId,
            'description' => $newDescription,
        ]);
    }

    public function givenTransactionWhenDeleteThenTransactionDeleteSuccessfully(FunctionalTester $I): void
    {
        $transactionId = $this->transaction->id->value;

        $this->bot->willStartConversation()
            ->hearText('/transactions')
            ->reply();

        $this->bot
            ->hearCallbackQueryData(CallbackData::data(TransactionCallback::DELETE, $transactionId))
            ->reply()
            ->assertReply('editMessageText', [
                'text' => $this->translator->trans('bot.transactions.deleteConfirm', [
                    '%amount%' => '450 ₽',
                    '%category%' => 'groceries',
                ]),
            ], 1);

        $this->bot
            ->hearCallbackQueryData(CallbackData::data(TransactionCallback::DELETE_OK, $transactionId))
            ->reply();

        $I->dontSeeInRepository(Transaction::class, ['id' => $transactionId]);
    }
}
