<?php

declare(strict_types=1);

namespace Test\Support\Fixture;

use App\Domain\Entity\Account;
use App\Domain\Entity\User;
use App\Domain\Enum\AccountType;
use App\Domain\ValueObject\TelegramId;
use DateTimeImmutable;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class OnboardedTelegramUserFixture implements FixtureInterface
{
    public const int TELEGRAM_ID = 123456789;

    public function load(ObjectManager $manager): void
    {
        $user = User::joinByTelegram(
            new TelegramId(self::TELEGRAM_ID),
            new DateTimeImmutable('2026-01-01'),
        );
        $account = Account::create($user, 'Основной', AccountType::Personal);
        $account->addDefaultCategories($user);

        $manager->persist($user);
        $manager->flush();
    }
}
