<?php

declare(strict_types=1);

namespace Test\Unit\Domain\Entity;

use App\Domain\Entity\Account;
use App\Domain\Entity\User;
use App\Domain\Enum\AccountType;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\TelegramId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Test\Unit\Builder\UserBuilder;

class UserTest extends TestCase
{
    public function testJoinByTelegram(): void
    {
        $user = User::joinByTelegram(
            $telegramId = new TelegramId(1232424),
            $date = new DateTimeImmutable(),
        );

        self::assertEquals($user->telegramId, $telegramId);
        self::assertEquals($user->createdAt, $date);
    }

    public function testAddAccount(): void
    {
        $user = new UserBuilder()->build();
        $account = new Account(
            Id::generate(),
            'test',
            AccountType::Personal,
            new DateTimeImmutable(),
        );

        $user->addAccount($account);

        self::assertCount(1, $accounts = $user->getAccounts());
        self::assertEquals($account, $accounts[0] ?? null);
    }
}
