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

final class AddDefaultCategoriesTest extends TestCase
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
    public function givenUserHasAnEmptyAccountWhenTheUserAddsDefaultCategoriesThenTheAccountHasAllDefaultCategories(): void
    {
        $this->account->addDefaultCategories($this->accountCreator);

        $expected = Category::defaults();
        $categories = $this->account->getCategories();

        self::assertCount(count($expected), $categories);
        foreach ($expected as $index => [$type, $name]) {
            self::assertSame($type, $categories[$index]->type);
            self::assertSame(mb_strtolower($name), $categories[$index]->name);
            self::assertSame($this->accountCreator, $categories[$index]->creator);
            self::assertSame($this->account, $categories[$index]->account);
        }
    }

    #[Test]
    public function givenUserHasAnEmptyAccountWhenInaccessibleUserAddsDefaultCategoriesThenAnExceptionIsExpectedAndTheAccountDoesNotHaveCategories(): void
    {
        $this->expectException(DomainException::class);

        try {
            $this->account->addDefaultCategories(new UserBuilder()->build());
        } catch (DomainException $e) {
            self::assertCount(0, $this->account->getCategories());
            throw $e;
        }
    }

    #[Test]
    public function givenUserHasAnAccountWithCategoriesWhenTheUserAddsDefaultCategoriesThenAnExceptionIsExpectedAndTheAccountStillHasUnchangedCategories(): void
    {
        $this->account->addCategory($this->accountCreator, TransactionType::Expense, 'food');
        $existing = $this->account->getCategories()[0];

        $this->expectException(DomainException::class);

        try {
            $this->account->addDefaultCategories($this->accountCreator);
        } catch (DomainException $e) {
            self::assertCount(1, $categories = $this->account->getCategories());
            self::assertSame($existing, $categories[0]);
            throw $e;
        }
    }
}
