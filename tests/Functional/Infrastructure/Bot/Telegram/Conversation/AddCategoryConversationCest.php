<?php

declare(strict_types=1);

namespace Test\Functional\Infrastructure\Bot\Telegram\Conversation;

use App\Domain\Entity\Category;
use App\Domain\Entity\User;
use App\Domain\Enum\TransactionType;
use App\Application\Gateway\TranslatorInterface;
use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Testing\FakeNutgram;
use Test\Support\Fixture\OnboardedTelegramUserFixture;
use Test\Support\FunctionalTester;
use Test\Support\TelegramBotTester;

final class AddCategoryConversationCest
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
    public function givenUserHasAnAccountWhenAddsCategoryWithAllCorrectStepsThenAllStepsHasSuccessAndTheAccountHasTheCategory(
        FunctionalTester $I
    ): void {
        $telegramId = OnboardedTelegramUserFixture::TELEGRAM_ID;

        $this->bot->willStartConversation()
            ->hearText('/category')
            ->reply()
            ->assertReplyText('Categories');

        $this->bot
            ->hearCallbackQueryData('cat:type:expense')
            ->reply()
            ->assertReply('editMessageText', index: 1);

        $this->bot
            ->hearCallbackQueryData('cat:add:expense')
            ->reply()
            ->assertReply('sendMessage', ['text' => 'Enter the category name:'], 1)
            ->assertActiveConversation($telegramId, $telegramId);

        $this->bot->hearText('Subscriptions')
            ->reply()
            ->assertReplyText('Category "Subscriptions" (expense) added.')
            ->assertNoConversation($telegramId, $telegramId);

        $user = $I->grabEntityFromRepository(User::class, ['telegramId' => $telegramId]);
        $account = $user->getAccounts()[0];

        $I->seeInRepository(Category::class, [
            'account' => $account,
            'name' => 'subscriptions',
            'type' => TransactionType::Expense,
        ]);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function givenDuplicateCategoryWhenAddsThenConversationEndsAndErrorIsShown(FunctionalTester $I): void
    {
        $telegramId = OnboardedTelegramUserFixture::TELEGRAM_ID;

        $this->bot->willStartConversation()
            ->hearText('/category')
            ->reply();

        $this->bot
            ->hearCallbackQueryData('cat:type:expense')
            ->reply();

        $this->bot
            ->hearCallbackQueryData('cat:add:expense')
            ->reply()
            ->assertActiveConversation($telegramId, $telegramId);

        $this->bot->hearText('groceries')
            ->reply()
            ->assertReplyText($I->grabService(TranslatorInterface::class)->trans('error.accountManage'))
            ->assertNoConversation($telegramId, $telegramId);
    }
}
