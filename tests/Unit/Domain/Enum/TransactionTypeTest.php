<?php

namespace Test\Unit\Domain\Enum;

use App\Domain\Enum\TransactionType;
use App\Domain\Exception\EnumInvalidValueException;
use Codeception\Test\Unit;

class TransactionTypeTest extends Unit
{
    public function givenTypeNameWhenMakeTransactionTypeThenExpectedTransactionTypeIsCreatedOrExceptionWhenWrongName(): void
    {
        self::assertEquals(TransactionType::Expense, TransactionType::fromName('expense'));
        self::assertEquals(TransactionType::Income, TransactionType::fromName('income'));

        $this->expectException(EnumInvalidValueException::class);
        TransactionType::fromName('wrong');
    }

    public function givenTypeSignWhenMakeTransactionTypeThenExpectedTransactionTypeIsCreatedOrExceptionWhenUnsupportedSign(): void
    {
        self::assertEquals(TransactionType::Expense, TransactionType::fromSign('-'));
        self::assertEquals(TransactionType::Income, TransactionType::fromSign('+'));

        $this->expectException(EnumInvalidValueException::class);
        TransactionType::fromSign('*');
    }
}
