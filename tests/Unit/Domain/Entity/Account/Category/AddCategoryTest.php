<?php

declare(strict_types=1);

namespace Test\Unit\Domain\Entity\Account\Category;

use App\Domain\Entity\Account;
use App\Domain\Entity\User;
use App\Domain\Enum\TransactionType;
use App\Domain\Exception\AccountManageException;
use App\Domain\Exception\NoAccessException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Test\Unit\Builder\AccountBuilder;
use Test\Unit\Builder\UserBuilder;

final class AddCategoryTest extends TestCase
{
    private User $accountCreator;
    private Account $account;

    protected function setUp(): void
    {
        $this->accountCreator = new UserBuilder()->build();
        $this->account = new AccountBuilder()->withUser($this->accountCreator)->build();

        parent::setUp();
    }

    #[Test]
    public function givenUserHasAnAccountWhenTheUserAddsACorrectCategoryThenTheAccountHasTheCategory(): void
    {
        $this->account->addCategory($this->accountCreator, $type = TransactionType::Expense, $name = 'food');

        self::assertCount(1, $this->account->getCategories());
        $category = $this->account->getCategories()[0];
        self::assertEquals($category->type, $type);
        self::assertEquals($category->name, mb_strtolower($name));
        self::assertEquals($this->account, $category->account);
        self::assertEquals($category->creator, $this->accountCreator);
    }

    #[Test]
    public function givenUserHasAnAccountWhenInaccessibleUserAddsACorrectCategoryThenAnExceptionIsExpectedAndTheAccountDoesNotHaveTheCategory(): void
    {
        $this->expectException(NoAccessException::class);

        try {
            $this->account->addCategory(new UserBuilder()->build(), TransactionType::Expense, 'food');
        } catch (NoAccessException $e) {
            self::assertCount(0, $this->account->getCategories());
            throw $e;
        }
    }

    #[Test]
    public function givenUserHasAnAccountWhenTheUserAddsADuplicateCategoryThenAnExceptionIsExpectedAndTheAccountStillHasOnlyTheOriginalCategory(): void
    {
        $this->account->addCategory($this->accountCreator, TransactionType::Expense, 'food');
        $category = $this->account->getCategories()[0];

        $this->expectException(AccountManageException::class);

        try {
            $this->account->addCategory($this->accountCreator, TransactionType::Expense, 'food');
        } catch (AccountManageException $e) {
            self::assertCount(1, $this->account->getCategories());
            self::assertEquals($category, $this->account->getCategories()[0]);
            throw $e;
        }
    }
}
