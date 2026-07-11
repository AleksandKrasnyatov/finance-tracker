<?php

declare(strict_types=1);

namespace Test\Unit\Domain\Entity\Account\Transaction;

use App\Domain\Entity\Account;
use App\Domain\Entity\Category;
use App\Domain\Entity\User;
use App\Domain\Enum\AccountType;
use App\Domain\Enum\TransactionType;
use App\Domain\ValueObject\Money;
use DomainException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Test\Unit\Builder\AccountBuilder;
use Test\Unit\Builder\UserBuilder;

class AddTransactionTest extends TestCase
{
    private User $accountCreator;
    private Account $account;
    private Category $accountCategory;

    protected function setUp(): void
    {
        $this->accountCreator = new UserBuilder()->build();
        $this->account = new AccountBuilder()->withUser($this->accountCreator)->build();
        $this->account->addCategory($this->accountCreator, TransactionType::Expense, 'food');
        $this->accountCategory = $this->account->getCategories()[0];

        parent::setUp();
    }

    #[Test]
    public function givenUserHasAnAccountWithACategoryWhenTheUserAddsACorrectTransactionThanTheAccountHasTheTransaction(): void
    {
        $this->account->addTransaction(
            $this->accountCreator,
            $this->accountCategory,
            $money = new Money('100.00'),
            $description = 'test description'
        );

        self::assertCount(1, $this->account->getTransactions());
        $transaction = $this->account->getTransactions()[0];
        self::assertEquals($this->account, $transaction->account);
        self::assertEquals($transaction->category, $this->accountCategory);
        self::assertEquals($transaction->money, $money);
        self::assertEquals($transaction->creator, $this->accountCreator);
        self::assertEquals($transaction->description, $description);
    }

    #[Test]
    public function givenUserHasAnAccountWithACategoryWhenTheUserAddsATransactionForWrongCategoryThanAnExceptionIsExpectedAndTheAccountDoesNotHaveTheTransaction(): void
    {
        $this->expectException(DomainException::class);

        $otherUser = new UserBuilder()->build();
        $otherAccount = Account::create($otherUser, 'other', AccountType::Personal);
        $otherAccount->addCategory($otherUser, TransactionType::Expense, 'food');
        $foreignCategory = $otherAccount->getCategories()[0];

        try {
            $this->account->addTransaction(
                $this->accountCreator,
                $foreignCategory,
                new Money('100.00')
            );
        } catch (DomainException $e) {
            self::assertCount(0, $this->account->getTransactions());
            throw $e;
        }
    }

    #[Test]
    public function givenUserHasAnAccountWithACategoryWhenUnaccessibleUserAddsACorrectTransactionThanAnExceptionIsExpectedAndTheAccountDoesNotHaveTheTransaction(): void
    {
        $this->expectException(DomainException::class);

        try {
            $this->account->addTransaction(
                new UserBuilder()->build(),
                $this->accountCategory,
                new Money('100.00')
            );
        } catch (DomainException $e) {
            self::assertCount(0, $this->account->getTransactions());
            throw $e;
        }
    }
}
