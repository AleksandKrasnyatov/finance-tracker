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
use App\Domain\Exception\AccountManageException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Test\Unit\Builder\AccountBuilder;
use Test\Unit\Builder\UserBuilder;

final class ChangeTransactionTypeTest extends TestCase
{
    private User $accountCreator;
    private Account $account;
    private Category $transactionCategory;
    private Category $accountAnotherCategory;
    private Transaction $transaction;

    protected function setUp(): void
    {
        $this->accountCreator = new UserBuilder()->build();
        $this->account = new AccountBuilder()->withUser($this->accountCreator)->build();
        $this->account->addCategory($this->accountCreator, TransactionType::Expense, 'food');
        $this->account->addCategory($this->accountCreator, TransactionType::Expense, 'shopping');
        $this->transactionCategory = $this->account->getCategories()[0];
        $this->accountAnotherCategory = $this->account->getCategories()[1];
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
    public function givenUserHasAnAccountWithATransactionWhenTheUserChangesTransactionCategoryThenTheTransactionHasNewCategoryOnly(): void
    {
        $this->account->changeTransactionCategory(
            $this->accountCreator,
            $this->transaction->id,
            $this->accountAnotherCategory,
        );

        self::assertCount(1, $this->account->getTransactions());
        $transaction = $this->account->getTransactions()[0];
        self::assertSame($this->transaction->id, $transaction->id);
        self::assertSame($this->accountAnotherCategory, $transaction->category);
        self::assertSame($this->accountCreator, $transaction->updater);
        self::assertCount(0, $this->transactionCategory->getTransactions());
        self::assertCount(1, $this->accountAnotherCategory->getTransactions());
        self::assertSame($transaction, $this->accountAnotherCategory->getTransactions()[0]);
    }

    #[Test]
    public function givenUserHasAnAccountWithATransactionWhenInaccessibleUserChangesTransactionCategoryThenAnExceptionIsExpectedAndTheTransactionStaysUnchanged(): void
    {
        $this->expectException(NoAccessException::class);

        try {
            $this->account->changeTransactionCategory(
                new UserBuilder()->build(),
                $this->transaction->id,
                $this->accountAnotherCategory,
            );
        } catch (NoAccessException $e) {
            self::assertCount(1, $transactions = $this->account->getTransactions());
            self::assertSame($this->transactionCategory, $transactions[0]->category);
            self::assertNull($transactions[0]->updater);
            self::assertCount(1, $this->transactionCategory->getTransactions());
            self::assertCount(0, $this->accountAnotherCategory->getTransactions());
            throw $e;
        }
    }

    #[Test]
    public function givenUserHasAnAccountWithATransactionWhenTheUserChangesTransactionCategoryForWrongCategoryThenAnExceptionIsExpectedAndTheTransactionStaysUnchanged(): void
    {
        $otherAccount = new AccountBuilder()
            ->withUser(new UserBuilder()->build())
            ->withCategory(TransactionType::Expense, 'food')
            ->build();

        $foreignCategory = $otherAccount->getCategories()[0];

        $this->expectException(AccountManageException::class);

        try {
            $this->account->changeTransactionCategory(
                $this->accountCreator,
                $this->transaction->id,
                $foreignCategory,
            );
        } catch (AccountManageException $e) {
            self::assertCount(1, $transactions = $this->account->getTransactions());
            self::assertSame($this->transactionCategory, $transactions[0]->category);
            self::assertNull($transactions[0]->updater);
            self::assertCount(1, $this->transactionCategory->getTransactions());
            throw $e;
        }
    }

    #[Test]
    public function givenUserHasAnAccountWithATransactionWhenTheUserChangesTransactionCategoryForUnknownTransactionThenAnExceptionIsExpectedAndTheTransactionStaysUnchanged(): void
    {
        $this->expectException(AccountManageException::class);

        try {
            $this->account->changeTransactionCategory(
                $this->accountCreator,
                Id::generate(),
                $this->accountAnotherCategory,
            );
        } catch (AccountManageException $e) {
            self::assertCount(1, $transactions = $this->account->getTransactions());
            self::assertSame($this->transactionCategory, $transactions[0]->category);
            self::assertNull($transactions[0]->updater);
            self::assertCount(1, $this->transactionCategory->getTransactions());
            self::assertCount(0, $this->accountAnotherCategory->getTransactions());
            throw $e;
        }
    }
}
