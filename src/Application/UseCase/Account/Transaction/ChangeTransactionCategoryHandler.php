<?php

declare(strict_types=1);

namespace App\Application\UseCase\Account\Transaction;

use App\Domain\Repository\AccountRepositoryInterface;
use App\Domain\Repository\CategoryRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Id;
use App\Infrastructure\Repository\Flusher;

final readonly class ChangeTransactionCategoryHandler
{
    public function __construct(
        private UserRepositoryInterface $users,
        private AccountRepositoryInterface $accounts,
        private CategoryRepositoryInterface $categories,
        private Flusher $flusher,
    ) {
    }

    public function handle(ChangeTransactionCategoryCommand $command): void
    {
        $user = $this->users->get(new Id($command->userId));
        $account = $this->accounts->get(new Id($command->accountId));
        $category = $this->categories->get(new Id($command->categoryId));

        $account->changeTransactionCategory($user, new Id($command->transactionId), $category);

        $this->flusher->flush();
    }
}
