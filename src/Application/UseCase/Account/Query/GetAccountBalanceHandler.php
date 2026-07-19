<?php

declare(strict_types=1);

namespace App\Application\UseCase\Account\Query;

use App\Application\Fetcher\AccountBalanceFetcherInterface;
use App\Domain\Exception\NoAccessException;
use App\Domain\Repository\AccountRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Id;

final readonly class GetAccountBalanceHandler
{
    public function __construct(
        private UserRepositoryInterface $users,
        private AccountRepositoryInterface $accounts,
        private AccountBalanceFetcherInterface $fetcher,
    ) {
    }

    public function handle(GetAccountBalanceQuery $query): GetAccountBalanceResult
    {
        $user = $this->users->get(new Id($query->userId));
        $account = $this->accounts->get(new Id($query->accountId));

        if (!$account->canView($user)) {
            throw new NoAccessException('Can not view this account.');
        }

        $balance = $this->fetcher->fetchCurrentMonth($account->id);

        return new GetAccountBalanceResult(
            $balance->year,
            $balance->month,
            $balance->balance,
            $balance->incomes,
            $balance->expenses,
            $balance->currency
        );
    }
}
