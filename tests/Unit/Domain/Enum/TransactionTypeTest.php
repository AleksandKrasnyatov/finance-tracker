<?php

namespace Test\Unit\Domain\Enum;

use App\Domain\Enum\TransactionType;
use Codeception\Test\Unit;
use DomainException;

class TransactionTypeTest extends Unit
{
    public function givenTypeNameWhenMakeTransactionTypeThenExpectedTransactionTypeIsCreatedOrExceptionWhenWrongName(): void
    {
        self::assertEquals(TransactionType::Expense, TransactionType::fromName('expense'));
        self::assertEquals(TransactionType::Income, TransactionType::fromName('income'));

        $this->expectException(DomainException::class);
        TransactionType::fromName('wrong');
    }

    public function givenTypeSignWhenMakeTransactionTypeThenExpectedTransactionTypeIsCreatedOrExceptionWhenUnsupportedSign(): void
    {
        self::assertEquals(TransactionType::Expense, TransactionType::fromSign('-'));
        self::assertEquals(TransactionType::Income, TransactionType::fromSign('+'));

        $this->expectException(DomainException::class);
        TransactionType::fromSign('*');
    }
}
