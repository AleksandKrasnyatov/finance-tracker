<?php

declare(strict_types=1);

namespace Test\Unit\Infrastructure\Bot\Telegram\Conversation;

use App\Domain\Entity\Account;
use App\Domain\Entity\User;
use App\Domain\Enum\TransactionType;
use App\Domain\Repository\AccountRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\TelegramId;
use App\Infrastructure\Bot\Telegram\TelegramBot;
use DI\Container;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SergiX44\Nutgram\Configuration;
use SergiX44\Nutgram\Telegram\Properties\UpdateType;
use SergiX44\Nutgram\Telegram\Types\Chat\Chat;
use SergiX44\Nutgram\Telegram\Types\User\User as TelegramUser;
use SergiX44\Nutgram\Testing\FakeNutgram;
use Test\Unit\Builder\AccountBuilder;
use Test\Unit\Builder\UserBuilder;

final class AddCategoryConversationTest extends TestCase
{
    private int $telegramId = 123456789;
    private User $user;
    private Account $account;
    private FakeNutgram $bot;

    protected function setUp(): void
    {
        $this->user = new UserBuilder()->withTelegramId(new TelegramId($this->telegramId))->build();
        $this->account = new AccountBuilder()->withUser($this->user)->build();
        $this->bot = $this->createBot();
        parent::setUp();
    }

    #[Test]
    public function givenGivenUserHasAnAccountWhenTheUserAddsACategoryFromBotWithCorrectAnswersThenAllStepsHasSuccessAndTheAccountHasTheCategory(): void
    {
        $this->bot->willStartConversation()
            ->hearUpdateType(UpdateType::MESSAGE, [
                'text' => '/category',
                'from' => ['id' => $this->telegramId, 'is_bot' => false, 'first_name' => 'Alex'],
                'chat' => ['id' => $this->telegramId, 'type' => 'private'],
            ])
            ->reply()
            ->assertReplyText('Какой тип категории?')
            ->assertActiveConversation($this->telegramId, $this->telegramId);

        $this->bot->hearCallbackQueryData('expense')
            ->reply()
            ->assertReply('sendMessage', ['text' => 'Введите название категории:'], 1);

        $this->bot->hearUpdateType(UpdateType::MESSAGE, [
            'text' => 'Подписки',
            'from' => ['id' => $this->telegramId, 'is_bot' => false, 'first_name' => 'Alex'],
            'chat' => ['id' => $this->telegramId, 'type' => 'private'],
        ])
            ->reply()
            ->assertReplyText('Категория «Подписки» (расход) добавлена.')
            ->assertNoConversation($this->telegramId, $this->telegramId);

        self::assertTrue($this->account->hasCategoryWithParams('Подписки', TransactionType::Expense));
    }

    private function createBot(): FakeNutgram
    {
        $users = $this->createStub(UserRepositoryInterface::class);
        $users->method('getByTelegramId')->willReturn($this->user);
        $users->method('get')->willReturn($this->user);

        $accounts = $this->createStub(AccountRepositoryInterface::class);
        $accounts->method('get')->willReturn($this->account);

        $entityManager = $this->createStub(EntityManagerInterface::class);

        $container = new Container();
        $container->set(UserRepositoryInterface::class, $users);
        $container->set(AccountRepositoryInterface::class, $accounts);
        $container->set(EntityManagerInterface::class, $entityManager);

        $bot = FakeNutgram::instance(
            config: new Configuration(container: $container),
        );

        $telegramUser = new TelegramUser($bot);
        $telegramUser->id = $this->telegramId;
        $telegramUser->is_bot = false;
        $telegramUser->first_name = 'Alex';

        $chat = new Chat($bot);
        $chat->id = $this->telegramId;
        $chat->type = 'private';

        $bot->setCommonUser($telegramUser);
        $bot->setCommonChat($chat);

        new TelegramBot($bot)->configure();

        return $bot;
    }
}
