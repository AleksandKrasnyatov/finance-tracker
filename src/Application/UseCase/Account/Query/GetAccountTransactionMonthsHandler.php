<?php

declare(strict_types=1);

namespace App\Application\UseCase\Account\Query;

use App\Application\Fetcher\Account\AccountTransactionMonthsFetcherInterface;
use App\Domain\Exception\NoAccessException;
use App\Domain\Repository\AccountRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Id;

final readonly class GetAccountTransactionMonthsHandler
{
    public function __construct(
        private UserRepositoryInterface $users,
        private AccountRepositoryInterface $accounts,
        private AccountTransactionMonthsFetcherInterface $fetcher,
    ) {
    }

    public function handle(GetAccountTransactionMonthsQuery $query): GetAccountTransactionMonthsResult
    {
        $user = $this->users->get(new Id($query->userId));
        $account = $this->accounts->get(new Id($query->accountId));

        if (!$account->canView($user)) {
            throw new NoAccessException('Can not view this account.');
        }

        $page = max(1, $query->page);
        $perPage = max(1, $query->perPage);
        $offset = ($page - 1) * $perPage;

        $items = $this->fetcher->fetch($account->id, $offset, $perPage + 1);
        $hasNext = count($items) > $perPage;
        $months = array_slice($items, 0, $perPage);

        return new GetAccountTransactionMonthsResult(
            months: $months,
            page: $page,
            hasPrev: $page > 1,
            hasNext: $hasNext,
        );
    }
}
