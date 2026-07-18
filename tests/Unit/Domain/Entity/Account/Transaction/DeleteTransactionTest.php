<?php

declare(strict_types=1);

namespace Test\Unit\Domain\Entity\Account\Transaction;

use App\Domain\Entity\Account;
use App\Domain\Entity\Category;
use App\Domain\Entity\Transaction;
use App\Domain\Entity\User;
use App\Domain\Enum\TransactionType;
use App\Domain\Exception\NoAccessException;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\Money;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Test\Unit\Builder\AccountBuilder;
use Test\Unit\Builder\UserBuilder;

final class DeleteTransactionTest extends TestCase
{
    private User $accountCreator;
    private Account $account;
    private Category $transactionCategory;
    private Transaction $transaction;

    protected function setUp(): void
    {
        $this->accountCreator = new UserBuilder()->build();
        $this->account = new AccountBuilder()->withUser($this->accountCreator)->build();
        $this->account->addCategory($this->accountCreator, TransactionType::Expense, 'food');
        $this->transactionCategory = $this->account->getCategories()[0];
        $this->account->addTransaction(
            $this->accountCreator,
            $this->transactionCategory,
            new Money('100.00'),
            'lunch',
        );
        $this->transaction = $this->account->getTransactions()[0];

        parent::setUp();
    }

    #[Test]
    public function givenUserHasAnAccountWithATransactionWhenTheUserDeletesTheTransactionThenTheAccountAndCategoryStayWithoutTheTransaction(): void
    {
        $this->account->deleteTransaction($this->accountCreator, $this->transaction->id);

        self::assertCount(0, $this->account->getTransactions());
        self::assertCount(0, $this->transactionCategory->getTransactions());
    }

    #[Test]
    public function givenUserHasAnAccountWithATransactionWhenInaccessibleUserDeletesTheTransactionThenAnExceptionIsExpectedAndTheTransactionStaysUnchanged(): void
    {
        $this->expectException(NoAccessException::class);

        try {
            $this->account->deleteTransaction(new UserBuilder()->build(), $this->transaction->id);
        } catch (NoAccessException $e) {
            self::assertCount(1, $this->account->getTransactions());
            self::assertSame($this->transaction, $this->account->getTransactions()[0]);
            self::assertCount(1, $this->transactionCategory->getTransactions());
            throw $e;
        }
    }

    #[Test]
    public function givenUserHasAnAccountWhenTheUserDeletesUnknownOrAlreadyDeletedTransactionThenNoExceptionsAndTheAccountStaysConsistent(): void
    {
        $transactionId = $this->transaction->id;
        $this->account->deleteTransaction($this->accountCreator, $transactionId);
        $this->account->deleteTransaction($this->accountCreator, $transactionId);
        $this->account->deleteTransaction($this->accountCreator, Id::generate());

        self::assertCount(0, $this->account->getTransactions());
        self::assertCount(0, $this->transactionCategory->getTransactions());
    }
}
