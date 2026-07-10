<?php

declare(strict_types=1);

namespace App\Application\UseCase\Account\Transaction;

use App\Domain\Enum\TransactionType;
use App\Domain\Repository\AccountRepositoryInterface;
use App\Domain\Repository\CategoryRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\Money;
use App\Infrastructure\Repository\Flusher;
use DomainException;

final readonly class AddTransactionHandler
{
    public function __construct(
        private UserRepositoryInterface $users,
        private AccountRepositoryInterface $accounts,
        private CategoryRepositoryInterface $categories,
        private Flusher $flusher,
    ) {
    }

    public function handle(AddTransactionCommand $command): void
    {
        $user = $this->users->get(new Id($command->userId));
        $account = $this->accounts->get(new Id($command->accountId));

        if (!$type = TransactionType::tryFrom($command->type)) {
            throw new DomainException('Invalid transaction type');
        }
        $category = $this->categories->getByParams($account, $command->category, $type);
        $money = new Money($command->amount);

        $account->addTransaction($user, $category, $money, $command->description);

        $this->flusher->flush();
    }
}
