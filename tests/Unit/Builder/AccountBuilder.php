<?php

declare(strict_types=1);

namespace Test\Unit\Builder;

use App\Domain\Entity\Account;
use App\Domain\Entity\User;
use App\Domain\Enum\AccountType;
use App\Domain\Enum\TransactionType;
use App\Domain\ValueObject\Id;

final class AccountBuilder
{
    private Id $id;
    private string $name;
    private AccountType $type;

    private ?User $user = null;
    /**
     * @var list<array{TransactionType, string}>
     */
    private array $categories = [];

    public function __construct()
    {
        $this->id = Id::generate();
        $this->name = 'test_account';
        $this->type = AccountType::Personal;
    }

    public function withUser(User $user): self
    {
        $clone = clone $this;
        $clone->user = $user;
        return $clone;
    }

    public function withCategory(TransactionType $type, string $name): self
    {
        $clone = clone $this;
        $clone->categories[] = [$type, $name];
        return $clone;
    }


    public function build(): Account
    {
        if ($this->user !== null) {
            $account = Account::create($this->user, $this->name, $this->type);

            foreach ($this->categories as $categoryData) {
                $account->addCategory($this->user, $categoryData[0], $categoryData[1]);
            }

            return $account;
        }

        return new Account(
            $this->id,
            $this->name,
            $this->type,
        );
    }
}
