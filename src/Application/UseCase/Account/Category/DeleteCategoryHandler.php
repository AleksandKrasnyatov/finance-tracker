<?php

declare(strict_types=1);

namespace App\Application\UseCase\Account\Category;

use App\Domain\Repository\AccountRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Id;
use App\Infrastructure\Repository\Flusher;

final readonly class DeleteCategoryHandler
{
    public function __construct(
        private UserRepositoryInterface $users,
        private AccountRepositoryInterface $accounts,
        private Flusher $flusher,
    ) {
    }

    public function handle(ChangeCategoryNameCommand $command): void
    {
        $user = $this->users->get(new Id($command->userId));
        $account = $this->accounts->get(new Id($command->accountId));

        $account->deleteCategory($user, new Id($command->categoryId));

        $this->flusher->flush();
    }
}
