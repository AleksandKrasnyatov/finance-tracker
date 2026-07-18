<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Account;
use App\Domain\Entity\Category;
use App\Domain\Enum\TransactionType;
use App\Domain\Exception\EntityNotFoundException;
use App\Domain\ValueObject\Id;

interface CategoryRepositoryInterface
{
    public function get(Id $id): Category;
    /**
     * @throws EntityNotFoundException<Category>
     */
    public function getByParams(Account $account, string $name, TransactionType $type): Category;
}
