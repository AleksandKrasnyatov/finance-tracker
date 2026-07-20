<?php

declare(strict_types=1);

namespace App\Application\UseCase\Account\Query;

use App\Application\Fetcher\AccountCategoriesFetcherInterface;
use App\Domain\Exception\NoAccessException;
use App\Domain\Repository\AccountRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Id;

final readonly class GetAccountCategoriesHandler
{
    public function __construct(
        private UserRepositoryInterface $users,
        private AccountRepositoryInterface $accounts,
        private AccountCategoriesFetcherInterface $fetcher,
    ) {
    }

    public function handle(GetAccountCategoriesQuery $query): GetAccountCategoriesResult
    {
        $user = $this->users->get(new Id($query->userId));
        $account = $this->accounts->get(new Id($query->accountId));

        if (!$account->canView($user)) {
            throw new NoAccessException('Can not view this account.');
        }

        $categories = $this->fetcher->fetch($account->id);

        return new GetAccountCategoriesResult(
            $categories->incomes,
            $categories->expenses,
        );
    }
}
