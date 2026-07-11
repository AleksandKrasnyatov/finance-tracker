<?php

declare(strict_types=1);

namespace Test\Unit\Domain\Entity\Account;

use App\Domain\Entity\Account;
use App\Domain\Enum\AccountType;
use App\Domain\ValueObject\Id;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Test\Unit\Builder\UserBuilder;

final class CreateAccountTest extends TestCase
{
    #[Test]
    public function givenValidDataWhenAccountIsCreatedThenAccountBelongsToOwnerAndCanBeManaged(): void
    {
        $owner = new UserBuilder()->build();
        $account = Account::create(
            $owner,
            $name = 'TeSt',
            $type = AccountType::Personal,
        );

        self::assertEquals(mb_strtolower($name), $account->name);
        self::assertEquals($type, $account->type);
        self::assertTrue($account->canManage($owner));
        self::assertCount(1, $owner->getAccounts());
        self::assertEquals($account, $owner->getAccounts()[0]);
    }

    #[Test]
    public function givenEmptyNameWhenAccountIsCreatedThenInvalidArgumentExceptionIsExpected(): void
    {
        self::expectException(InvalidArgumentException::class);

        new Account(
            Id::generate(),
            '',
            AccountType::Personal,
        );
    }
}
