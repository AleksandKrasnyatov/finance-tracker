<?php

declare(strict_types=1);

namespace Test\Unit\Domain\Entity;

use App\Domain\Entity\Account;
use App\Domain\Entity\User;
use App\Domain\Enum\AccountType;
use App\Domain\ValueObject\TelegramId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Test\Unit\Builder\AccountBuilder;
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

    public function testAddAccountFactoryMethod(): void
    {
        $user = new UserBuilder()->build();
        $account = Account::create($user, 'test', AccountType::Joint);

        self::assertCount(1, $accounts = $user->getAccounts());
        self::assertEquals($account, $accounts[0] ?? null);
        self::assertTrue($account->canManage($user));
        self::assertCount(1, $account->getMembers());
    }

    public function testAddAccountDirectly(): void
    {
        $user = new UserBuilder()->build();
        $account = new AccountBuilder()->build();

        $user->addAccount($account);

        self::assertCount(1, $accounts = $user->getAccounts());
        self::assertEquals($account, $accounts[0] ?? null);
        self::assertTrue($account->canManage($user));
        self::assertCount(1, $account->getMembers());
    }
}
