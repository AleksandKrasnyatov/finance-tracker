<?php

declare(strict_types=1);

namespace App\Application\UseCase\Account\Query;

use App\Application\Fetcher\AccountCategoriesFetcherInterface;
use App\Domain\Entity\Category;
use App\Domain\Exception\EntityNotFoundException;
use App\Domain\Exception\NoAccessException;
use App\Domain\Repository\AccountRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Id;

final readonly class GetAccountCategoryHandler
{
    public function __construct(
        private UserRepositoryInterface $users,
        private AccountRepositoryInterface $accounts,
        private AccountCategoriesFetcherInterface $fetcher,
    ) {
    }

    public function handle(GetAccountCategoryQuery $query): GetAccountCategoryResult
    {
        $user = $this->users->get(new Id($query->userId));
        $account = $this->accounts->get(new Id($query->accountId));

        if (!$account->canView($user)) {
            throw new NoAccessException('Can not view this account.');
        }

        $category = $this->fetcher->fetchOne($account, new Id($query->categoryId));
        if ($category === null) {
            throw new EntityNotFoundException(Category::class);
        }

        return new GetAccountCategoryResult($category);
    }
}
