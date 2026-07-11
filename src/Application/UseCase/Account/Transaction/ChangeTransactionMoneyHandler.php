<?php

declare(strict_types=1);

namespace App\Application\UseCase\Account\Transaction;

use App\Domain\Enum\Currency;
use App\Domain\Repository\AccountRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\Money;
use App\Infrastructure\Repository\Flusher;
use DomainException;

final readonly class ChangeTransactionMoneyHandler
{
    public function __construct(
        private UserRepositoryInterface $users,
        private AccountRepositoryInterface $accounts,
        private Flusher $flusher,
    ) {
    }

    public function handle(ChangeTransactionMoneyCommand $command): void
    {
        $user = $this->users->get(new Id($command->userId));
        $account = $this->accounts->get(new Id($command->accountId));

        $money = new Money($command->amount);

        $account->changeTransactionMoney($user, new Id($command->transactionId), $money);

        $this->flusher->flush();
    }
}
