<?php

declare(strict_types=1);

namespace Test\Unit\Domain\Entity;

use App\Domain\Entity\Account;
use App\Domain\Enum\AccountType;
use App\Domain\ValueObject\Id;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class AccountTest extends TestCase
{
    public function testSuccess(): void
    {
        $account = new Account(
            $id = Id::generate(),
            $name = 'TeSt',
            $type = AccountType::Personal,
            $date = new DateTimeImmutable(),
        );

        self::assertEquals($account->id, $id);
        self::assertEquals($account->name, mb_strtolower($name));
        self::assertEquals($account->type, $type);
        self::assertEquals($account->createdAt, $date);
    }

    public function testException(): void
    {
        self::expectException(InvalidArgumentException::class);

        $account = new Account(
            Id::generate(),
            '',
            AccountType::Personal,
            new DateTimeImmutable(),
        );
    }
}
