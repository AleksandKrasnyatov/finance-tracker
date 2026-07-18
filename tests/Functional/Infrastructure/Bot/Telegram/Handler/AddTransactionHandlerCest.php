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
            ->hearText('-150,50 groceries')
            ->reply()
            ->assertReplyText('Recorded -150.50 in "groceries" (expense).');

        $transaction = $I->grabEntityFromRepository(Transaction::class, [
            'description' => '',
            'category' => [
                'name' => 'groceries',
                'type' => TransactionType::Expense,
            ],
        ]);
        $I->assertSame('150.50', $transaction->money->amount);
    }

    public function givenUserHasCategoryWhenAddsIncomeThenTransactionIsPersisted(FunctionalTester $I): void
    {
        $this->bot
            ->hearText('+100000 salary')
            ->reply()
            ->assertReplyText('Recorded +100000 in "salary" (income).');

        $I->seeInRepository(Transaction::class, [
            'category' => [
                'name' => 'salary',
                'type' => TransactionType::Income,
            ],
        ]);
    }

    public function givenUserHasCategoryWhenAddsExpenseWithCommentThenCommentIsPersisted(FunctionalTester $I): void
    {
        $this->bot
            ->hearText('-100 groceries lunch with colleagues')
            ->reply()
            ->assertReplyText('Recorded -100 in "groceries" (expense) (lunch with colleagues).');

        $I->seeInRepository(Transaction::class, [
            'description' => 'lunch with colleagues',
            'category' => [
                'name' => 'groceries',
                'type' => TransactionType::Expense,
            ],
        ]);
    }

    public function givenAmbiguousCategoryNameWhenAddsIncomeThenIncomeCategoryIsUsed(FunctionalTester $I): void
    {
        $this->bot
            ->hearText('+50 other')
            ->reply()
            ->assertReplyText('Recorded +50 in "other" (income).');

        $I->seeInRepository(Transaction::class, [
            'category' => [
                'name' => 'other',
                'type' => TransactionType::Income,
            ],
        ]);
    }

    public function givenUnknownCategoryWhenAddsExpenseThenErrorAndNothingPersisted(FunctionalTester $I): void
    {
        $this->bot
            ->hearText('-100 unknown')
            ->reply()
            ->assertReplyText(
                'Category not found.',
            );

        $I->dontSeeInRepository(Transaction::class, []);
    }
}
