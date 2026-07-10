<?php

declare(strict_types=1);

namespace Test\Unit\Builder;

use App\Domain\Entity\Account;
use App\Domain\Entity\User;
use App\Domain\Enum\AccountType;
use App\Domain\ValueObject\Id;

final class AccountBuilder
{
    private Id $id;
    private string $name;
    private AccountType $type;

    private ?User $user = null;

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


    public function build(): Account
    {
        if ($this->user !== null) {
            return Account::create($this->user, $this->name, $this->type);
        }

        return new Account(
            $this->id,
            $this->name,
            $this->type,
        );
    }
}
