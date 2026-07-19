<?php

declare(strict_types=1);

namespace Test\Support\Fixture;

use App\Domain\Entity\Account;
use App\Domain\Entity\Category;
use App\Domain\Entity\User;
use App\Domain\Enum\AccountType;
use App\Domain\Enum\TransactionType;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\TelegramId;
use DateTimeImmutable;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use RuntimeException;

final class OnboardedTelegramUserWithBalanceFixture implements FixtureInterface
{
    public const int TELEGRAM_ID = 123456789;
    public const int INCOMES = 12596;
    public const int EXPENSES = 1000;
    public const int BALANCE = 11596;

    public function load(ObjectManager $manager): void
    {
        $user = User::joinByTelegram(
            new TelegramId(self::TELEGRAM_ID),
            new DateTimeImmutable('2026-01-01'),
        );
        $account = Account::create($user, 'main', AccountType::Personal);
        $account->addDefaultCategories($user, Category::defaults());

        $income = $this->category($account, 'salary', TransactionType::Income);
        $expense = $this->category($account, 'groceries', TransactionType::Expense);

        $account->addTransaction(
            $user,
            $income,
            new Money((string)self::INCOMES),
            date: new DateTimeImmutable('first day of last month'),
        );
        $account->addTransaction(
            $user,
            $income,
            new Money((string)self::INCOMES),
            date: new DateTimeImmutable('today'),
        );
        $account->addTransaction(
            $user,
            $expense,
            new Money((string)self::EXPENSES),
            date: new DateTimeImmutable('today'),
        );

        $manager->persist($user);
        $manager->flush();
    }

    private function category(Account $account, string $name, TransactionType $type): Category
    {
        foreach ($account->getCategories() as $category) {
            if ($category->name === $name && $category->type === $type) {
                return $category;
            }
        }

        throw new RuntimeException(sprintf('Category "%s" (%s) not found.', $name, $type->value));
    }
}
