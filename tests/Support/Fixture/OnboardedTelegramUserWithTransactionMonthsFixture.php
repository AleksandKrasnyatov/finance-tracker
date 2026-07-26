<?php

declare(strict_types=1);

namespace Test\Support\Fixture;

use App\Application\Service\SeedCatalog;
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

final class OnboardedTelegramUserWithTransactionMonthsFixture implements FixtureInterface
{
    public const int TELEGRAM_ID = 123456789;

    public function load(ObjectManager $manager): void
    {
        $user = User::joinByTelegram(
            new TelegramId(self::TELEGRAM_ID),
            new DateTimeImmutable('2026-01-01'),
        );
        $account = Account::create($user, 'main', AccountType::Personal, SeedCatalog::ACCOUNT_CODE);
        $account->addDefaultCategories($user, Category::defaults());

        $income = $this->category($account, 'salary', TransactionType::Income);

        $account->addTransaction(
            $user,
            $income,
            new Money('100'),
            date: new DateTimeImmutable('2026-07-15'),
        );
        $account->addTransaction(
            $user,
            $income,
            new Money('200'),
            date: new DateTimeImmutable('2024-06-10'),
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
