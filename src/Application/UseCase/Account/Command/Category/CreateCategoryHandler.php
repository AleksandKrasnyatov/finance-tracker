<?php

declare(strict_types=1);

namespace App\Application\UseCase\Account\Command\Category;

use App\Domain\Enum\TransactionType;
use App\Domain\Repository\AccountRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Id;
use App\Infrastructure\Repository\Flusher;
use DomainException;

final readonly class CreateCategoryHandler
{
    public function __construct(
        private UserRepositoryInterface $users,
        private AccountRepositoryInterface $accounts,
        private Flusher $flusher,
    ) {
    }

    public function handle(CreateCategoryCommand $command): void
    {
        $user = $this->users->get(new Id($command->userId));
        $account = $this->accounts->get(new Id($command->accountId));
        if (!$type = TransactionType::tryFrom($command->type)) {
            throw new DomainException('Invalid transaction type');
        }

        $account->addCategory($user, $type, $command->name);

        $this->flusher->flush();
    }
}
