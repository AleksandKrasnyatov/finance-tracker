<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Account;
use App\Domain\Entity\Category;
use App\Domain\Enum\TransactionType;
use DomainException;

interface CategoryRepositoryInterface
{
    /**
     * @throws DomainException
     */
    public function getByParams(Account $account, string $name, TransactionType $type): Category;
}
