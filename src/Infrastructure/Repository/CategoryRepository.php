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
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function get(Id $id): Category
    {
        $entity = $this->entityManager->getRepository(Category::class)->find($id->value);
        if ($entity === null) {
            throw new DomainException('Category is not found.');
        }
        return $entity;
    }

    public function getByParams(Account $account, string $name, TransactionType $type): Category
    {
        $entity = $this->entityManager
            ->getRepository(Category::class)
            ->findOneBy([
                'account' => $account,
                'name' => mb_strtolower($name),
                'type' => $type,
            ]);

        if ($entity === null) {
            throw new DomainException('Category is not found.');
        }

        return $entity;
    }
}
