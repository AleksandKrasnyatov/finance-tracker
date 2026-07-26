<?php

declare(strict_types=1);

namespace App\Application\UseCase\Account\Query;

use App\Application\Fetcher\Account\AccountTransactionsFetcherInterface;
use App\Domain\Enum\TransactionType;
use App\Domain\Exception\NoAccessException;
use App\Domain\Repository\AccountRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Id;

final readonly class GetAccountTransactionsHandler
{
    public function __construct(
        private UserRepositoryInterface $users,
        private AccountRepositoryInterface $accounts,
        private AccountTransactionsFetcherInterface $fetcher,
    ) {
    }

    public function handle(GetAccountTransactionsQuery $query): GetAccountTransactionsResult
    {
        $user = $this->users->get(new Id($query->userId));
        $account = $this->accounts->get(new Id($query->accountId));

        if (!$account->canView($user)) {
            throw new NoAccessException('Can not view this account.');
        }

        $type = $query->filter === null ? null : TransactionType::fromName($query->filter);

        return new GetAccountTransactionsResult(
            $this->fetcher->fetch($account->id, $query->year, $query->month, $type),
        );
    }
}
