<?php

declare(strict_types=1);

use App\Application\Fetcher\Account\AccountBalanceFetcherInterface;
use App\Application\Fetcher\Account\AccountCategoriesFetcherInterface;
use App\Application\Fetcher\Account\AccountTransactionMonthsFetcherInterface;
use App\Domain\Repository\AccountRepositoryInterface;
use App\Domain\Repository\CategoryRepositoryInterface;
use App\Infrastructure\Fetcher\AccountBalanceFetcher;
use App\Infrastructure\Fetcher\AccountCategoriesFetcher;
use App\Infrastructure\Fetcher\AccountTransactionMonthsFetcher;
use App\Infrastructure\Repository\AccountRepository;
use App\Infrastructure\Repository\CategoryRepository;

use function DI\autowire;

return [
    CategoryRepositoryInterface::class => autowire(CategoryRepository::class),
    AccountRepositoryInterface::class => autowire(AccountRepository::class),
    AccountBalanceFetcherInterface::class => autowire(AccountBalanceFetcher::class),
    AccountCategoriesFetcherInterface::class => autowire(AccountCategoriesFetcher::class),
    AccountTransactionMonthsFetcherInterface::class => autowire(AccountTransactionMonthsFetcher::class),
];
