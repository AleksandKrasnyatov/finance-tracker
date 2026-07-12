<?php

declare(strict_types=1);

namespace Test\Unit\Infrastructure\Bot\Telegram\Handler;

use App\Domain\Entity\Account;
use App\Domain\Entity\Category;
use App\Domain\Entity\User;
use App\Domain\Enum\TransactionType;
use App\Domain\Repository\AccountRepositoryInterface;
use App\Domain\Repository\CategoryRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\TelegramId;
use App\Infrastructure\Bot\Telegram\TelegramBot;
use DI\Container;
use Doctrine\ORM\EntityManagerInterface;
use DomainException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SergiX44\Nutgram\Configuration;
use SergiX44\Nutgram\Testing\FakeNutgram;
use SergiX44\Nutgram\Telegram\Types\Chat\Chat;
use SergiX44\Nutgram\Telegram\Types\User\User as TelegramUser;
use Test\Unit\Builder\AccountBuilder;
use Test\Unit\Builder\UserBuilder;

final class AddTransactionHandlerTest extends TestCase
{
    private int $telegramId = 987654321;
    private User $user;
    private Account $account;
    private FakeNutgram $bot;

    protected function setUp(): void
    {
        $this->user = new UserBuilder()->withTelegramId(new TelegramId($this->telegramId))->build();
        $this->account = new AccountBuilder()
            ->withUser($this->user)
            ->withCategory(TransactionType::Expense, 'продукты')
            ->withCategory(TransactionType::Income, 'зарплата')
            ->withCategory(TransactionType::Income, 'другое')
            ->withCategory(TransactionType::Expense, 'другое')
            ->build();
        $this->bot = $this->createBot();

        parent::setUp();
    }

    #[Test]
    public function givenGivenUserHasAnAccountWithTheCategoryWhenTheUserAddsExpenseForTheCategoryFromBotMessageThenExpenseTransactionIsCreated(): void
    {
        $this->bot
            ->hearText('-150,50 продукты')
            ->reply()
            ->assertReplyText('Записал -150.50 в «продукты» (расход).');

        self::assertCount(1, $this->account->getTransactions());
        $transaction = $this->account->getTransactions()[0];
        self::assertSame('150.50', $transaction->money->amount);
        self::assertSame(TransactionType::Expense, $transaction->category->type);
        self::assertSame('продукты', $transaction->category->name);
        self::assertSame('', $transaction->description);
    }

    #[Test]
    public function givenGivenUserHasAnAccountWithTheCategoryWhenTheUserAddsIncomeForTheCategoryFromBotMessageThenIncomeTransactionIsCreated(): void
    {
        $this->bot
            ->hearText('+100000 зарплата')
            ->reply()
            ->assertReplyText('Записал +100000 в «зарплата» (доход).');

        self::assertSame(TransactionType::Income, $this->account->getTransactions()[0]->category->type);
    }

    #[Test]
    public function givenGivenUserHasAnAccountWithTheCategoryWhenTheUserAddsExpenseForTheCategoryWithCommentFromBotMessageThenExpenseTransactionIsCreatedWithThatComment(): void
    {
        $this->bot
            ->hearText('-100 продукты обед с коллегами')
            ->reply()
            ->assertReplyText('Записал -100 в «продукты» (расход). Комментарий: обед с коллегами');

        self::assertSame('обед с коллегами', $this->account->getTransactions()[0]->description);
    }

    #[Test]
    public function givenGivenUserHasAnAccountWithTheAmbiguousCategoriesWhenTheUserAddsIncomeForTheCategoryFromBotMessageThenIncomeTransactionIsCreatedWithCorrectCategory(): void
    {
        $this->bot
            ->hearText('+50 другое')
            ->reply()
            ->assertReplyText('Записал +50 в «другое» (доход).');

        self::assertSame(TransactionType::Income, $this->account->getTransactions()[0]->category->type);
    }

    #[Test]
    public function givenGivenUserHasAnAccountWithThCategoryWhenTheUserAddsExpenseForUnknownCategoryFromBotMessageThenUserSeesError(): void
    {
        $this->bot
            ->hearText('-100 неизвестная')
            ->reply()
            ->assertReplyText('Категория не найдена. Проверьте название и знак (+ доход / − расход) или добавьте её через «Добавить категорию».');

        self::assertCount(0, $this->account->getTransactions());
    }

    private function createBot(): FakeNutgram
    {
        $users = $this->createStub(UserRepositoryInterface::class);
        $users->method('getByTelegramId')->willReturn($this->user);
        $users->method('get')->willReturn($this->user);

        $accounts = $this->createStub(AccountRepositoryInterface::class);
        $accounts->method('get')->willReturn($this->account);

        $categories = $this->createStub(CategoryRepositoryInterface::class);
        $categories->method('getByParams')->willReturnCallback(
            function (Account $account, string $name, TransactionType $type): Category {
                $name = mb_strtolower($name);
                foreach ($account->getCategories() as $category) {
                    if ($category->name === $name && $category->type === $type) {
                        return $category;
                    }
                }

                throw new DomainException('Category is not found.');
            },
        );

        $entityManager = $this->createStub(EntityManagerInterface::class);

        $container = new Container();
        $container->set(UserRepositoryInterface::class, $users);
        $container->set(AccountRepositoryInterface::class, $accounts);
        $container->set(CategoryRepositoryInterface::class, $categories);
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
