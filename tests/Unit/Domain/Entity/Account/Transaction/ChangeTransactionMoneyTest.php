<?php

declare(strict_types=1);

namespace Test\Unit\Domain\Entity\Account\Transaction;

use App\Domain\Entity\Account;
use App\Domain\Entity\Category;
use App\Domain\Entity\Transaction;
use App\Domain\Entity\User;
use App\Domain\Enum\Currency;
use App\Domain\Enum\TransactionType;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\Money;
use DomainException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Test\Unit\Builder\AccountBuilder;
use Test\Unit\Builder\UserBuilder;

final class ChangeTransactionMoneyTest extends TestCase
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
    public function givenUserHasAnAccountWithATransactionWhenTheUserChangesTransactionMoneyThanTheTransactionHasNewMoneyOnly(): void
    {
        $this->account->changeTransactionMoney(
            $this->accountCreator,
            $this->transaction->id,
            $money = new Money('250.50', Currency::USD),
        );

        self::assertCount(1, $this->account->getTransactions());
        $transaction = $this->account->getTransactions()[0];
        self::assertSame($this->transaction->id, $transaction->id);
        self::assertEquals($money, $transaction->money);
        self::assertSame($this->transactionCategory, $transaction->category);
        self::assertSame($this->accountCreator, $transaction->updater);
    }

    #[Test]
    public function givenUserHasAnAccountWithATransactionWhenUnaccessibleUserChangesTransactionMoneyThanAnExceptionIsExpectedAndTheTransactionStaysUnchanged(): void
    {
        $this->expectException(DomainException::class);

        try {
            $this->account->changeTransactionMoney(
                new UserBuilder()->build(),
                $this->transaction->id,
                new Money('250.50'),
            );
        } catch (DomainException $e) {
            self::assertCount(1, $transactions = $this->account->getTransactions());
            self::assertEquals($this->transaction->money, $transactions[0]->money);
            self::assertNull($transactions[0]->updater);
            throw $e;
        }
    }

    #[Test]
    public function givenUserHasAnAccountWithATransactionWhenTheUserChangesMoneyForUnknownTransactionThanAnExceptionIsExpectedAndTheTransactionStaysUnchanged(): void
    {
        $this->expectException(DomainException::class);

        try {
            $this->account->changeTransactionMoney(
                $this->accountCreator,
                Id::generate(),
                new Money('250.50'),
            );
        } catch (DomainException $e) {
            self::assertCount(1, $transactions = $this->account->getTransactions());
            self::assertEquals($this->transaction->money, $transactions[0]->money);
            self::assertNull($transactions[0]->updater);
            throw $e;
        }
    }
}
