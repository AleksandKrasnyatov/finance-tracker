<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Schema\PrimaryKeyConstraint;
use Doctrine\DBAL\Types\Exception\TypesException;
use Doctrine\Migrations\Metadata\Storage\TableMetadataStorageConfiguration;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Doctrine\ORM\Tools\ToolEvents;
use Override;

final readonly class MigrationSchemaSubscriber implements EventSubscriber
{
    public function __construct(
        private TableMetadataStorageConfiguration $configuration
    ) {
    }

    #[Override]
    public function getSubscribedEvents(): array
    {
        return [
            ToolEvents::postGenerateSchema => 'postGenerateSchema',
        ];
    }

    /**
     * @throws TypesException
     */
    public function postGenerateSchema(GenerateSchemaEventArgs $args): void
    {
        $table = $args->getSchema()->createTable($this->configuration->getTableName());

        $table->addColumn(
            $this->configuration->getVersionColumnName(),
            'string',
            ['notnull' => true, 'length' => $this->configuration->getVersionColumnLength()],
        );

        $table->addColumn($this->configuration->getExecutedAtColumnName(), 'datetime', ['notnull' => false]);
        $table->addColumn($this->configuration->getExecutionTimeColumnName(), 'integer', ['notnull' => false]);

        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames($this->configuration->getVersionColumnName())
                ->create(),
        );
    }
}
