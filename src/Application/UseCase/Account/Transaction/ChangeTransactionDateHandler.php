<?php

declare(strict_types=1);

namespace App\Application\UseCase\Account\Transaction;

use App\Domain\Repository\AccountRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Id;
use App\Infrastructure\Repository\Flusher;
use DateMalformedStringException;
use DateTimeImmutable;

final readonly class ChangeTransactionDateHandler
{
    public function __construct(
        private UserRepositoryInterface $users,
        private AccountRepositoryInterface $accounts,
        private Flusher $flusher,
    ) {
    }

    /**
     * @throws DateMalformedStringException
     */
    public function handle(ChangeTransactionDateCommand $command): void
    {
        $user = $this->users->get(new Id($command->userId));
        $account = $this->accounts->get(new Id($command->accountId));
        $date = new DateTimeImmutable($command->date);

        $account->changeTransactionDate($user, new Id($command->transactionId), $date);

        $this->flusher->flush();
    }
}
