<?php

declare(strict_types=1);

namespace App\Application\UseCase\Account\Query;

use App\Application\Fetcher\Account\AccountTransactionsFetcherInterface;
use App\Domain\Entity\Transaction;
use App\Domain\Exception\EntityNotFoundException;
use App\Domain\Exception\NoAccessException;
use App\Domain\Repository\AccountRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Id;

final readonly class GetAccountTransactionHandler
{
    public function __construct(
        private UserRepositoryInterface $users,
        private AccountRepositoryInterface $accounts,
        private AccountTransactionsFetcherInterface $fetcher,
    ) {
    }

    public function handle(GetAccountTransactionQuery $query): GetAccountTransactionResult
    {
        $user = $this->users->get(new Id($query->userId));
        $account = $this->accounts->get(new Id($query->accountId));

        if (!$account->canView($user)) {
            throw new NoAccessException('Can not view this account.');
        }

        $transaction = $this->fetcher->fetchOne($account->id, new Id($query->transactionId));
        if ($transaction === null) {
            throw new EntityNotFoundException(Transaction::class);
        }

        return new GetAccountTransactionResult($transaction);
    }
}
