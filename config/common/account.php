<?php

declare(strict_types=1);

use App\Domain\Repository\AccountRepositoryInterface;
use App\Domain\Repository\CategoryRepositoryInterface;
use App\Infrastructure\Repository\AccountRepository;
use App\Infrastructure\Repository\CategoryRepository;

use function DI\autowire;

return [
    CategoryRepositoryInterface::class => autowire(CategoryRepository::class),
    AccountRepositoryInterface::class => autowire(AccountRepository::class),
];
