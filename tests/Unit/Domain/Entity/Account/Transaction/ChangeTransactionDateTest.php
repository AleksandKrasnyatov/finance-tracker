<?php

declare(strict_types=1);

namespace Test\Unit\Domain\Entity\Account\Transaction;

use App\Domain\Entity\Account;
use App\Domain\Entity\Category;
use App\Domain\Entity\Transaction;
use App\Domain\Entity\User;
use App\Domain\Enum\TransactionType;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\Money;
use DateTimeImmutable;
use DomainException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Test\Unit\Builder\AccountBuilder;
use Test\Unit\Builder\UserBuilder;

final class ChangeTransactionDateTest extends TestCase
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
            new DateTimeImmutable('2026-01-15'),
        );
        $this->transaction = $this->account->getTransactions()[0];

        parent::setUp();
    }

    #[Test]
    public function givenUserHasAnAccountWithATransactionWhenTheUserChangesTransactionDateThanTheTransactionHasNewDateOnly(): void
    {
        $this->account->changeTransactionDate(
            $this->accountCreator,
            $this->transaction->id,
            $date = new DateTimeImmutable('2026-07-01'),
        );

        self::assertCount(1, $this->account->getTransactions());
        $transaction = $this->account->getTransactions()[0];
        self::assertSame($this->transaction->id, $transaction->id);
        self::assertEquals($date, $transaction->date);
        self::assertEquals($this->transaction->money, $transaction->money);
        self::assertSame($this->transactionCategory, $transaction->category);
        self::assertSame($this->accountCreator, $transaction->updater);
    }

    #[Test]
    public function givenUserHasAnAccountWithATransactionWhenUnaccessibleUserChangesTransactionDateThanAnExceptionIsExpectedAndTheTransactionStaysUnchanged(): void
    {
        $this->expectException(DomainException::class);

        try {
            $this->account->changeTransactionDate(
                new UserBuilder()->build(),
                $this->transaction->id,
                new DateTimeImmutable('2026-07-01'),
            );
        } catch (DomainException $e) {
            self::assertCount(1, $transactions = $this->account->getTransactions());
            self::assertEquals($this->transaction->date, $transactions[0]->date);
            self::assertNull($transactions[0]->updater);
            throw $e;
        }
    }

    #[Test]
    public function givenUserHasAnAccountWithATransactionWhenTheUserChangesDateForUnknownTransactionThanAnExceptionIsExpectedAndTheTransactionStaysUnchanged(): void
    {
        $this->expectException(DomainException::class);

        try {
            $this->account->changeTransactionDate(
                $this->accountCreator,
                Id::generate(),
                new DateTimeImmutable('2026-07-01'),
            );
        } catch (DomainException $e) {
            self::assertCount(1, $transactions = $this->account->getTransactions());
            self::assertEquals($this->transaction->date, $transactions[0]->date);
            self::assertNull($transactions[0]->updater);
            throw $e;
        }
    }
}
