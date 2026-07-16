<?php

declare(strict_types=1);

namespace Test\Support\Helper;

use Codeception\Lib\Interfaces\DoctrineProvider;
use Codeception\Module;
use Codeception\TestInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Psr\Container\ContainerInterface;

final class Application extends Module implements DoctrineProvider
{
    private static bool $schemaReady = false;

    private ?ContainerInterface $container = null;

    public function _beforeSuite($settings = []): void
    {
        $this->bootContainer();
        $this->recreateSchema();
        self::$schemaReady = true;
    }

    public function _before(TestInterface $test): void
    {
        $this->bootContainer();

        if (!self::$schemaReady) {
            $this->recreateSchema();
            self::$schemaReady = true;
        }
    }

    public function _getEntityManager(): EntityManagerInterface
    {
        if ($this->container === null) {
            $this->bootContainer();
        }

        if (!self::$schemaReady) {
            $this->recreateSchema();
            self::$schemaReady = true;
        }

        return $this->grabService(EntityManagerInterface::class);
    }

    public function grabService(string $id): mixed
    {
        if ($this->container === null) {
            $this->bootContainer();
        }

        return $this->container->get($id);
    }

    private function bootContainer(): void
    {
        $this->container = require codecept_root_dir('config/container.php');
    }

    private function recreateSchema(): void
    {
        $entityManager = $this->grabService(EntityManagerInterface::class);
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }
}
