<?php

declare(strict_types=1);

namespace App\Application\UseCase\Account;

use App\Application\Service\SeedCatalog;
use App\Domain\Entity\Account;
use App\Domain\Enum\AccountType;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Id;
use App\Infrastructure\Repository\Flusher;

final readonly class CreateUserAccountHandler
{
    public function __construct(
        private UserRepositoryInterface $users,
        private SeedCatalog $seeds,
        private Flusher $flusher,
    ) {
    }

    public function handle(CreateUserAccountCommand $command): void
    {
        $user = $this->users->get(new Id($command->userId));

        Account::create(
            $user,
            $command->name ?? $this->seeds->accountName($user->locale),
            AccountType::tryFrom($command->type) ?? AccountType::Personal,
        );

        $this->flusher->flush();
    }
}
