<?php

declare(strict_types=1);

namespace Test\Support\Fixture;

use App\Domain\Entity\Account;
use App\Domain\Entity\Reminder;
use App\Domain\Entity\User;
use App\Domain\Enum\AccountType;
use App\Domain\Enum\Locale;
use App\Domain\Enum\TransactionType;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\ReminderTime;
use App\Domain\ValueObject\TelegramId;
use App\Domain\ValueObject\Timezone;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use RuntimeException;

final class ReminderCandidatesFixture implements FixtureInterface
{
    public const string NOW = '2026-07-23 18:00:00';

    public const int MATCH_UTC = 1001;
    public const int MATCH_MOSCOW = 1002;
    public const int MATCH_OLD_TRANSACTION = 1003;
    public const int MATCH_REMINDED_YESTERDAY = 1004;

    public const int SKIP_WRONG_TIME = 1005;
    public const int SKIP_DISABLED = 1006;
    public const int SKIP_REMINDED_TODAY = 1007;
    public const int SKIP_TRANSACTION_TODAY = 1008;
    public const int SKIP_MOSCOW_TRANSACTION_TODAY = 1009;

    /** @var list<int> */
    public const array EXPECTED_TELEGRAM_IDS = [
        self::MATCH_UTC,
        self::MATCH_MOSCOW,
        self::MATCH_OLD_TRANSACTION,
        self::MATCH_REMINDED_YESTERDAY,
    ];

    public function load(ObjectManager $manager): void
    {
        if (!$manager instanceof EntityManagerInterface) {
            throw new RuntimeException('EntityManagerInterface is required to backdate created_at.');
        }

        $matchOldTransaction = $this->user(
            $manager,
            self::MATCH_OLD_TRANSACTION,
            $this->reminder('18:00', 'UTC'),
        );
        $skipTransactionToday = $this->user(
            $manager,
            self::SKIP_TRANSACTION_TODAY,
            $this->reminder('18:00', 'UTC'),
        );
        $skipMoscowTransactionToday = $this->user(
            $manager,
            self::SKIP_MOSCOW_TRANSACTION_TODAY,
            $this->reminder('21:00', 'Europe/Moscow'),
            Locale::Ru,
        );

        $this->user($manager, self::MATCH_UTC, $this->reminder('18:00', 'UTC'));
        $this->user($manager, self::MATCH_MOSCOW, $this->reminder('21:00', 'Europe/Moscow'), Locale::Ru);
        $this->user(
            $manager,
            self::MATCH_REMINDED_YESTERDAY,
            $this->reminder(
                '18:00',
                'UTC',
                lastReminderSentAt: new DateTimeImmutable('2026-07-22 12:00:00', new DateTimeZone('UTC')),
            ),
        );
        $this->user($manager, self::SKIP_WRONG_TIME, $this->reminder('19:00', 'UTC'));
        $this->user($manager, self::SKIP_DISABLED, $this->reminder('18:00', 'UTC', enabled: false));
        $this->user(
            $manager,
            self::SKIP_REMINDED_TODAY,
            $this->reminder(
                '18:00',
                'UTC',
                lastReminderSentAt: new DateTimeImmutable('2026-07-23 10:00:00', new DateTimeZone('UTC')),
            ),
        );

        $manager->flush();

        $this->addTransactionWithCreatedAt(
            $manager,
            $matchOldTransaction,
            new DateTimeImmutable('2026-07-22 23:59:00', new DateTimeZone('UTC')),
        );
        $this->addTransactionWithCreatedAt(
            $manager,
            $skipTransactionToday,
            new DateTimeImmutable('2026-07-23 10:00:00', new DateTimeZone('UTC')),
        );
        $this->addTransactionWithCreatedAt(
            $manager,
            $skipMoscowTransactionToday,
            new DateTimeImmutable('2026-07-22 21:30:00', new DateTimeZone('UTC')),
        );
    }

    private function user(
        ObjectManager $manager,
        int $telegramId,
        Reminder $reminder,
        Locale $locale = Locale::En,
    ): User {
        $user = new User(
            Id::generate(),
            new DateTimeImmutable('2026-01-01'),
            new TelegramId($telegramId),
            $locale,
            $reminder,
        );
        Account::create($user, 'main', AccountType::Personal);
        $manager->persist($user);

        return $user;
    }

    private function addTransactionWithCreatedAt(
        EntityManagerInterface $manager,
        User $user,
        DateTimeImmutable $createdAt,
    ): void {
        $account = $user->getAccounts()[0];
        $account->addCategory($user, TransactionType::Expense, 'food');
        $account->addTransaction(
            $user,
            $account->getCategories()[0],
            new Money('10'),
        );
        $manager->flush();

        $manager->getConnection()->executeStatement(
            'UPDATE transactions SET created_at = :createdAt WHERE id = :id',
            [
                'createdAt' => $createdAt->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s'),
                'id' => $account->getTransactions()[0]->id->value,
            ],
        );
    }

    private function reminder(
        string $time,
        string $timezone,
        bool $enabled = true,
        ?DateTimeImmutable $lastReminderSentAt = null,
    ): Reminder {
        return new Reminder(
            new ReminderTime($time),
            new Timezone($timezone),
            $enabled,
            $lastReminderSentAt,
        );
    }
}
