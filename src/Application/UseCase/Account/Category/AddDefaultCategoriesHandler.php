<?php

declare(strict_types=1);

namespace App\Application\UseCase\Account\Category;

use App\Domain\Repository\AccountRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Id;
use App\Infrastructure\Repository\Flusher;

final readonly class AddDefaultCategoriesHandler
{
    public function __construct(
        private UserRepositoryInterface $users,
        private AccountRepositoryInterface $accounts,
        private Flusher $flusher,
    ) {
    }

    public function handle(AddDefaultCategoriesCommand $command): void
    {
        $user = $this->users->get(new Id($command->userId));
        $account = $this->accounts->get(new Id($command->accountId));

        $account->addDefaultCategories($user);

        $this->flusher->flush();
    }
}
