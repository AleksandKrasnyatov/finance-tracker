<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\TelegramId;
use Doctrine\ORM\EntityManagerInterface;
use DomainException;

final readonly class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function hasByTelegramId(TelegramId $telegramId): bool
    {
        return 0 !== $this->entityManager->getRepository(User::class)->count(['telegramId' => $telegramId->value]);
    }

    public function add(User $user): void
    {
        $this->entityManager->persist($user);
    }

    public function get(Id $id): User
    {
        $entity = $this->entityManager->getRepository(User::class)->find($id->value);
        if ($entity === null) {
            throw new DomainException('User is not found.');
        }
        return $entity;
    }

    public function getByTelegramId(TelegramId $telegramId): User
    {
        $entity = $this->entityManager->getRepository(User::class)->findOneBy([
            'telegramId' => $telegramId->value,
        ]);
        if ($entity === null) {
            throw new DomainException('User is not found.');
        }

        return $entity;
    }
}
