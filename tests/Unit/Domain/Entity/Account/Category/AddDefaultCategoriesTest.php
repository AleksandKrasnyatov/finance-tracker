<?php

declare(strict_types=1);

namespace Test\Unit\Domain\Entity\Account\Category;

use App\Domain\Dto\CategoryDto;
use App\Domain\Entity\Account;
use App\Domain\Entity\Category;
use App\Domain\Entity\User;
use App\Domain\Enum\TransactionType;
use App\Domain\Exception\NoAccessException;
use App\Domain\Exception\AccountManageException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Test\Unit\Builder\AccountBuilder;
use Test\Unit\Builder\UserBuilder;

final class AddDefaultCategoriesTest extends TestCase
{
    private User $accountCreator;
    private Account $account;

    /**
     * @var list<CategoryDto>
     */
    private array $defaultCategories;

    protected function setUp(): void
    {
        $this->accountCreator = new UserBuilder()->build();
        $this->account = new AccountBuilder()->withUser($this->accountCreator)->build();
        $this->defaultCategories = Category::defaults();

        parent::setUp();
    }

    #[Test]
    public function givenUserHasAnEmptyAccountWhenTheUserAddsDefaultCategoriesThenTheAccountHasAllDefaultCategories(): void
    {
        $this->account->addDefaultCategories($this->accountCreator, $this->defaultCategories);

        $categories = $this->account->getCategories();

        self::assertCount(count($this->defaultCategories), $categories);
        foreach ($this->defaultCategories as $index => $expected) {
            self::assertSame($expected->type, $categories[$index]->type);
            self::assertSame(mb_strtolower($expected->name), $categories[$index]->name);
            self::assertSame($this->accountCreator, $categories[$index]->creator);
            self::assertSame($this->account, $categories[$index]->account);
        }
    }

    #[Test]
    public function givenUserHasAnEmptyAccountWhenInaccessibleUserAddsDefaultCategoriesThenAnExceptionIsExpectedAndTheAccountDoesNotHaveCategories(): void
    {
        $this->expectException(NoAccessException::class);

        try {
            $this->account->addDefaultCategories(new UserBuilder()->build(), $this->defaultCategories);
        } catch (NoAccessException $e) {
            self::assertCount(0, $this->account->getCategories());
            throw $e;
        }
    }

    #[Test]
    public function givenUserHasAnAccountWithCategoriesWhenTheUserAddsDefaultCategoriesThenAnExceptionIsExpectedAndTheAccountStillHasUnchangedCategories(): void
    {
        $this->account->addCategory($this->accountCreator, TransactionType::Expense, 'food');
        $existing = $this->account->getCategories()[0];

        $this->expectException(AccountManageException::class);

        try {
            $this->account->addDefaultCategories($this->accountCreator, $this->defaultCategories);
        } catch (AccountManageException $e) {
            self::assertCount(1, $categories = $this->account->getCategories());
            self::assertSame($existing, $categories[0]);
            throw $e;
        }
    }
}
