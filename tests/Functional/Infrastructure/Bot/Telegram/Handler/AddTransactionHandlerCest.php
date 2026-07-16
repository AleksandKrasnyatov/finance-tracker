<?php

declare(strict_types=1);

namespace Test\Functional\Infrastructure\Bot\Telegram\Handler;

use App\Domain\Entity\Transaction;
use App\Domain\Enum\TransactionType;
use SergiX44\Nutgram\Testing\FakeNutgram;
use Test\Support\Fixture\OnboardedTelegramUserFixture;
use Test\Support\FunctionalTester;
use Test\Support\TelegramBotTester;

final class AddTransactionHandlerCest
{
    private FakeNutgram $bot;

    public function _before(FunctionalTester $I): void
    {
        $I->loadFixtures(OnboardedTelegramUserFixture::class);
        $this->bot = TelegramBotTester::configure($I, OnboardedTelegramUserFixture::TELEGRAM_ID);
    }

    public function givenUserHasCategoryWhenAddsExpenseThenTransactionIsPersisted(FunctionalTester $I): void
    {
        $this->bot
            ->hearText('-150,50 продукты')
            ->reply()
            ->assertReplyText('Записал -150.50 в «продукты» (расход).');

        $transaction = $I->grabEntityFromRepository(Transaction::class, [
            'description' => '',
            'category' => [
                'name' => 'продукты',
                'type' => TransactionType::Expense,
            ],
        ]);
        $I->assertSame('150.50', $transaction->money->amount);
    }

    public function givenUserHasCategoryWhenAddsIncomeThenTransactionIsPersisted(FunctionalTester $I): void
    {
        $this->bot
            ->hearText('+100000 зарплата')
            ->reply()
            ->assertReplyText('Записал +100000 в «зарплата» (доход).');

        $I->seeInRepository(Transaction::class, [
            'category' => [
                'name' => 'зарплата',
                'type' => TransactionType::Income,
            ],
        ]);
    }

    public function givenUserHasCategoryWhenAddsExpenseWithCommentThenCommentIsPersisted(FunctionalTester $I): void
    {
        $this->bot
            ->hearText('-100 продукты обед с коллегами')
            ->reply()
            ->assertReplyText('Записал -100 в «продукты» (расход). Комментарий: обед с коллегами');

        $I->seeInRepository(Transaction::class, [
            'description' => 'обед с коллегами',
            'category' => [
                'name' => 'продукты',
                'type' => TransactionType::Expense,
            ],
        ]);
    }

    public function givenAmbiguousCategoryNameWhenAddsIncomeThenIncomeCategoryIsUsed(FunctionalTester $I): void
    {
        $this->bot
            ->hearText('+50 другое')
            ->reply()
            ->assertReplyText('Записал +50 в «другое» (доход).');

        $I->seeInRepository(Transaction::class, [
            'category' => [
                'name' => 'другое',
                'type' => TransactionType::Income,
            ],
        ]);
    }

    public function givenUnknownCategoryWhenAddsExpenseThenErrorAndNothingPersisted(FunctionalTester $I): void
    {
        $this->bot
            ->hearText('-100 неизвестная')
            ->reply()
            ->assertReplyText('Категория не найдена. Проверьте название и знак (+ доход / − расход) или добавьте её через «Добавить категорию».');

        $I->dontSeeInRepository(Transaction::class, []);
    }
}
