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

    public function getByNameAndType(string $name, TransactionType $type): Category
    {
        $entity = $this->entityManager
            ->getRepository(Category::class)
            ->findOneBy([
                'name' => $name,
                'type' => $type->value,
            ]);

        if ($entity === null) {
            throw new DomainException('Category is not found.');
        }

        return $entity;
    }
}
