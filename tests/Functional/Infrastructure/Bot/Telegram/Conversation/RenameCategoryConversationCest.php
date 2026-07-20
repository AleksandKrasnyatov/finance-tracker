<?php

declare(strict_types=1);

namespace Test\Functional\Infrastructure\Bot\Telegram\Conversation;

use App\Domain\Entity\Category;
use App\Domain\Enum\TransactionType;
use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Testing\FakeNutgram;
use Test\Support\Fixture\OnboardedTelegramUserFixture;
use Test\Support\FunctionalTester;
use Test\Support\TelegramBotTester;

final class RenameCategoryConversationCest
{
    private FakeNutgram $bot;

    public function _before(FunctionalTester $I): void
    {
        $I->loadFixtures(OnboardedTelegramUserFixture::class);
        $this->bot = TelegramBotTester::configure($I, OnboardedTelegramUserFixture::TELEGRAM_ID);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function givenUserHasCategoryWhenRenamesThenNameIsUpdatedAndConversationEnds(
        FunctionalTester $I,
    ): void {
        $telegramId = OnboardedTelegramUserFixture::TELEGRAM_ID;
        $id = $I->grabEntityFromRepository(Category::class, [
            'name' => 'groceries',
            'type' => TransactionType::Expense,
        ])->id->value;

        $this->bot->willStartConversation()
            ->hearText('/category')
            ->reply()
            ->assertReplyText('Categories');

        $this->bot
            ->hearCallbackQueryData('cat:type:expense')
            ->reply();

        $this->bot
            ->hearCallbackQueryData('cat:view:' . $id)
            ->reply()
            ->assertReply('editMessageText', [
                'text' => 'groceries (expense)',
            ], 1);

        $this->bot
            ->hearCallbackQueryData('cat:rename:' . $id . ':expense')
            ->reply()
            ->assertReply('sendMessage', ['text' => 'Enter the new category name:'], 1)
            ->assertActiveConversation($telegramId, $telegramId);

        $this->bot
            ->hearText('Food')
            ->reply()
            ->assertReplyText('Category renamed to "food".')
            ->assertNoConversation($telegramId, $telegramId);

        $I->seeInRepository(Category::class, [
            'name' => 'food',
            'type' => TransactionType::Expense,
        ]);
        $I->dontSeeInRepository(Category::class, [
            'name' => 'groceries',
            'type' => TransactionType::Expense,
        ]);
    }
}
