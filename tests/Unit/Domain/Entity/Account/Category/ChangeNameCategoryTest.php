<?php

declare(strict_types=1);

namespace Test\Unit\Domain\Entity\Account\Category;

use App\Domain\Entity\Account;
use App\Domain\Entity\Category;
use App\Domain\Entity\User;
use App\Domain\Enum\TransactionType;
use DomainException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Test\Unit\Builder\AccountBuilder;
use Test\Unit\Builder\UserBuilder;

final class ChangeNameCategoryTest extends TestCase
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
    public function givenUserHasAnAccountWithCategoryWhenTheUserChangesNameOfTheCategoryThenTheCategoryHasNewNameOnly(): void
    {
        $categoryId = $this->accountCategory->id;
        $this->account->changeCategoryName($this->accountCreator, $categoryId, $newName = 'snaCks');

        self::assertCount(1, $this->account->getCategories());
        $category = $this->account->getCategories()[0];
        self::assertEquals($category->id, $categoryId);
        self::assertEquals($category->type, $this->accountCategory->type);
        self::assertEquals($category->name, mb_strtolower($newName));
        self::assertEquals($category->account, $this->account);
        self::assertEquals($category->creator, $this->accountCreator);
    }

    #[Test]
    public function givenUserHasAnAccountWithCategoryWhenInaccessibleUserChangesNameOfTheCategoryThenAnExceptionIsExpectedAndTheAccountStillHasUnchangedCategory(): void
    {
        $this->expectException(DomainException::class);

        try {
            $this->account->changeCategoryName(new UserBuilder()->build(), $this->accountCategory->id, 'snaCks');
        } catch (DomainException $e) {
            self::assertCount(1, $categories = $this->account->getCategories());
            self::assertEquals($categories[0], $this->accountCategory);
            throw $e;
        }
    }

    #[Test]
    public function givenUserHasAnAccountWithCategoryWhenTheUserChangesNameOfTheCategoryForTheCurrentNameThenTheAccountHasUnchangedCategory(): void
    {
        $categoryId = $this->accountCategory->id;
        $name = mb_ucfirst($this->accountCategory->name);
        $this->account->changeCategoryName($this->accountCreator, $categoryId, $name);

        self::assertCount(1, $this->account->getCategories());
        $category = $this->account->getCategories()[0];
        self::assertEquals($category->id, $categoryId);
        self::assertEquals($category->type, $this->accountCategory->type);
        self::assertEquals($category->name, $this->accountCategory->name);
        self::assertEquals($category->account, $this->account);
        self::assertEquals($category->creator, $this->accountCreator);
    }

    #[Test]
    public function givenUserHasAnAccountWithCategoryWhenTheUserChangesNameOfTheCategoryForNameThatAlreadyExistsThenAnExceptionIsExpectedAndTheAccountStillHasUnchangedCategories(): void
    {
        $this->account->addCategory($this->accountCreator, TransactionType::Expense, $newName = 'shopping');
        $secondExistsCategory = $this->account->getCategories()[1];

        $this->expectException(DomainException::class);

        try {
            $this->account->changeCategoryName($this->accountCreator, $this->accountCategory->id, $newName);
        } catch (DomainException $e) {
            self::assertCount(2, $categories = $this->account->getCategories());
            self::assertEquals($categories[0], $this->accountCategory);
            self::assertEquals($categories[1], $secondExistsCategory);
            throw $e;
        }
    }
}
