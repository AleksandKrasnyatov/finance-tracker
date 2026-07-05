<?php

declare(strict_types=1);

use App\Infrastructure\Console\HelloCommand;
use Doctrine\Migrations\Tools\Console\Command\ExecuteCommand;
use Doctrine\Migrations\Tools\Console\Command\LatestCommand;
use Doctrine\Migrations\Tools\Console\Command\ListCommand;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\Migrations\Tools\Console\Command\StatusCommand;
use Doctrine\Migrations\Tools\Console\Command\UpToDateCommand;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand;
use Doctrine\ORM\Tools\Console\EntityManagerProvider;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use Psr\Container\ContainerInterface;

return [
    EntityManagerProvider::class => static fn (ContainerInterface $container): EntityManagerProvider => new SingleManagerProvider($container->get(EntityManagerInterface::class)),
    ValidateSchemaCommand::class => static fn (ContainerInterface $container): ValidateSchemaCommand => new ValidateSchemaCommand($container->get(EntityManagerProvider::class)),

    'config' => [
        'console' => [
            'commands' => [
                HelloCommand::class,

                ValidateSchemaCommand::class,
                ExecuteCommand::class,
                MigrateCommand::class,
                LatestCommand::class,
                ListCommand::class,
                StatusCommand::class,
                UpToDateCommand::class,
            ]
        ]
    ]
];
