<?php

declare(strict_types=1);

namespace Test\Unit\Domain\Entity\Account;

use App\Domain\Entity\Account;
use App\Domain\Enum\AccountType;
use App\Domain\ValueObject\Id;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CreateAccountTest extends TestCase
{
    public function testSuccess(): void
    {
        $account = new Account(
            $id = Id::generate(),
            $name = 'TeSt',
            $type = AccountType::Personal,
        );

        self::assertEquals($account->id, $id);
        self::assertEquals($account->name, mb_strtolower($name));
        self::assertEquals($account->type, $type);
    }

    public function testException(): void
    {
        self::expectException(InvalidArgumentException::class);

        new Account(
            Id::generate(),
            '',
            AccountType::Personal,
        );
    }
}
