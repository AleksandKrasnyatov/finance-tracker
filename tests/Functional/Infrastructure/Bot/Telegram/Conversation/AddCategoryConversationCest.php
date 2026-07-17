<?php

declare(strict_types=1);

namespace Test\Functional\Infrastructure\Bot\Telegram\Conversation;

use App\Domain\Entity\Category;
use App\Domain\Entity\User;
use App\Domain\Enum\TransactionType;
use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Telegram\Properties\UpdateType;
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
    public function givenGivenUserHasAnAccountWhenTheUserAddsACategoryFromBotWithCorrectAnswersThenAllStepsHasSuccessAndTheAccountHasTheCategory(FunctionalTester $I): void
    {
        $telegramId =  OnboardedTelegramUserFixture::TELEGRAM_ID;

        $this->bot->willStartConversation()
            ->hearUpdateType(UpdateType::MESSAGE, [
                'text' => '/category',
                'from' => ['id' => $telegramId, 'is_bot' => false, 'first_name' => 'Alex'],
                'chat' => ['id' => $telegramId, 'type' => 'private'],
            ])
            ->reply()
            ->assertReplyText('Какой тип категории?')
            ->assertActiveConversation($telegramId, $telegramId);

        $this->bot->hearCallbackQueryData('expense')
            ->reply()
            ->assertReply('sendMessage', ['text' => 'Введите название категории:'], 1);

        $this->bot->hearUpdateType(UpdateType::MESSAGE, [
            'text' => $newCategoryName = 'Подписки',
            'from' => ['id' => $telegramId, 'is_bot' => false, 'first_name' => 'Alex'],
            'chat' => ['id' => $telegramId, 'type' => 'private'],
        ])
            ->reply()
            ->assertReplyText('Категория «Подписки» (расход) добавлена.')
            ->assertNoConversation($telegramId, $telegramId);

        $user = $I->grabEntityFromRepository(User::class, ['telegramId' => $telegramId]);
        $account = $user->getAccounts()[0];

        $I->seeInRepository(Category::class, [
            'account' => $account,
            'name' => mb_strtolower($newCategoryName),
            'type' => TransactionType::Expense,
        ]);
    }
}
