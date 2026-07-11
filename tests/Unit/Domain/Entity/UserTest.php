<?php

declare(strict_types=1);

namespace Test\Unit\Domain\Entity;

use App\Domain\Entity\Account;
use App\Domain\Entity\User;
use App\Domain\Enum\AccountType;
use App\Domain\ValueObject\TelegramId;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Test\Unit\Builder\AccountBuilder;
use Test\Unit\Builder\UserBuilder;

final class UserTest extends TestCase
{
    #[Test]
    public function givenTelegramIdWhenUserJoinsByTelegramThenUserHasTelegramIdAndCreatedAt(): void
    {
        $user = User::joinByTelegram(
            $telegramId = new TelegramId(1232424),
            $date = new DateTimeImmutable(),
        );

        self::assertEquals($user->telegramId, $telegramId);
        self::assertEquals($user->createdAt, $date);
    }

    #[Test]
    public function givenUserAndAccountWhenAccountIsAddedDirectlyThenUserAndAccountAreLinked(): void
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
