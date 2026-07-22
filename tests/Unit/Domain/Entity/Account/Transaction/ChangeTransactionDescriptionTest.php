<?php

declare(strict_types=1);

namespace Test\Unit\Domain\Entity\Account\Transaction;

use App\Domain\Entity\Account;
use App\Domain\Entity\Category;
use App\Domain\Entity\Transaction;
use App\Domain\Entity\User;
use App\Domain\Enum\TransactionType;
use App\Domain\Exception\AccountManageException;
use App\Domain\Exception\NoAccessException;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\Money;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Test\Unit\Builder\AccountBuilder;
use Test\Unit\Builder\UserBuilder;

final class ChangeTransactionDescriptionTest extends TestCase
{
    private User $accountCreator;
    private Account $account;
    private Transaction $transaction;

    protected function setUp(): void
    {
        $this->accountCreator = new UserBuilder()->build();
        $this->account = new AccountBuilder()->withUser($this->accountCreator)->build();
        $this->account->addCategory($this->accountCreator, TransactionType::Expense, 'food');
        $transactionCategory = $this->account->getCategories()[0];
        $this->account->addTransaction(
            $this->accountCreator,
            $transactionCategory,
            new Money('100.00'),
            'lunch',
        );
        $this->transaction = $this->account->getTransactions()[0];

        parent::setUp();
    }

    #[Test]
    public function givenUserHasAnAccountWithATransactionWhenTheUserChangesDescriptionThenOnlyDescriptionChanges(): void
    {
        $this->account->changeTransactionDescription(
            $this->accountCreator,
            $this->transaction->id,
            'dinner with friends',
        );

        self::assertCount(1, $this->account->getTransactions());
        $transaction = $this->account->getTransactions()[0];
        self::assertSame($this->transaction->id, $transaction->id);
        self::assertSame('dinner with friends', $transaction->description);
        self::assertSame($this->accountCreator, $transaction->updater);
    }

    #[Test]
    public function givenUserHasAnAccountWithATransactionWhenInaccessibleUserChangesDescriptionThenAnExceptionIsExpected(): void
    {
        $this->expectException(NoAccessException::class);

        try {
            $this->account->changeTransactionDescription(
                new UserBuilder()->build(),
                $this->transaction->id,
                'test description',
            );
        } catch (NoAccessException $e) {
            self::assertSame('lunch', $this->account->getTransactions()[0]->description);
            self::assertNull($this->account->getTransactions()[0]->updater);
            throw $e;
        }
    }

    #[Test]
    public function givenUserHasAnAccountWithATransactionWhenTheUserChangesDescriptionForUnknownTransactionThenAnExceptionIsExpected(): void
    {
        $this->expectException(AccountManageException::class);

        try {
            $this->account->changeTransactionDescription(
                $this->accountCreator,
                Id::generate(),
                'test description',
            );
        } catch (AccountManageException $e) {
            self::assertSame('lunch', $this->account->getTransactions()[0]->description);
            throw $e;
        }
    }
}
