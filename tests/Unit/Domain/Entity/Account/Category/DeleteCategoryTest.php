<?php

declare(strict_types=1);

namespace Test\Unit\Domain\Entity\Account\Category;

use App\Domain\Entity\Account;
use App\Domain\Entity\Category;
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

final class DeleteCategoryTest extends TestCase
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
    public function givenUserHasAnAccountWithCategoryWithoutTransactionsWhenTheUserDeletesTheCategoryThenTheAccountStaysWithoutTheCategory(): void
    {
        $categoryId = $this->accountCategory->id;
        $this->account->deleteCategory($this->accountCreator, $categoryId);

        self::assertCount(0, $this->account->getCategories());
    }

    #[Test]
    public function givenUserHasAnAccountWithCategoryWhenInaccessibleUserDeletesTheCategoryThenAnExceptionIsExpectedAndTheAccountStillHasUnchangedCategory(): void
    {
        $this->expectException(NoAccessException::class);

        try {
            $this->account->deleteCategory(new UserBuilder()->build(), $this->accountCategory->id);
        } catch (NoAccessException $e) {
            self::assertCount(1, $categories = $this->account->getCategories());
            self::assertEquals($categories[0], $this->accountCategory);
            throw $e;
        }
    }

    #[Test]
    public function givenUserHasAnAccountWhenTheUserDeletesMissingOrAlreadyDeletedCategoryThenNoExceptionsAndTheAccountDoesNotHaveAnyCategories(): void
    {
        $categoryId = $this->accountCategory->id;
        $this->account->deleteCategory($this->accountCreator, $categoryId);
        $this->account->deleteCategory($this->accountCreator, $categoryId);
        $this->account->deleteCategory($this->accountCreator, Id::generate());

        self::assertCount(0, $this->account->getCategories());
    }

    #[Test]
    public function givenUserHasAnAccountWithCategoryWithTransactionsWhenTheUserDeletesTheCategoryThenAnExceptionIsExpectedAndTheAccountStillHasUnchangedCategory(): void
    {
        $this->account->addTransaction($this->accountCreator, $this->accountCategory, new Money('12'));

        $this->expectException(AccountManageException::class);

        try {
            $this->account->deleteCategory($this->accountCreator, $this->accountCategory->id);
        } catch (AccountManageException $e) {
            self::assertCount(1, $categories = $this->account->getCategories());
            self::assertEquals($categories[0], $this->accountCategory);
            throw $e;
        }
    }
}
