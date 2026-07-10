<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Account;
use App\Domain\Entity\User;
use App\Domain\Repository\AccountRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\TelegramId;
use Doctrine\ORM\EntityManagerInterface;
use DomainException;

final readonly class AccountRepository implements AccountRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function get(Id $id): Account
    {
        $entity = $this->entityManager->getRepository(Account::class)->find($id->value);
        if ($entity === null) {
            throw new DomainException('Account is not found.');
        }
        return $entity;
    }
}
