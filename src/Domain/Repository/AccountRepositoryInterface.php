<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Account;
use App\Domain\ValueObject\Id;
use DomainException;

interface AccountRepositoryInterface
{
    /**
     * @throws DomainException
     */
    public function get(Id $id): Account;
}
