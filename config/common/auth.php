<?php

declare(strict_types=1);

use App\Domain\Repository\UserRepositoryInterface;

use App\Infrastructure\Repository\UserRepository;

use function DI\autowire;

return [
    UserRepositoryInterface::class => autowire(UserRepository::class),
];
