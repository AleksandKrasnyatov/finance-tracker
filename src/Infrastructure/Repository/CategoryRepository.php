<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Account;
use App\Domain\Entity\Category;
use App\Domain\Enum\TransactionType;
use App\Domain\Repository\CategoryRepositoryInterface;
use App\Domain\ValueObject\Id;
use Doctrine\ORM\EntityManagerInterface;
use DomainException;

final readonly class CategoryRepository implements CategoryRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function get(Id $id): Account
    {
        $user = $this->entityManager->getRepository(Account::class)->find($id->value);
        if ($user === null) {
            throw new DomainException('User is not found.');
        }
        return $user;
    }

    public function getByNameAndType(string $name, TransactionType $type): Category
    {
        $user = $this->entityManager
            ->getRepository(Category::class)
            ->findBy([
                'name' => $name,
                'type' => $type->value,
            ]);
        if ($user === null) {
            throw new DomainException('User is not found.');
        }
        return $user;
    }
}
