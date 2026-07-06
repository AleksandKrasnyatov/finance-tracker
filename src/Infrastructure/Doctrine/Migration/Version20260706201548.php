<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260706201548 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE accounts (id UUID NOT NULL, name VARCHAR(100) NOT NULL, type VARCHAR(8) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE user_accounts (id UUID NOT NULL, joined_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, account_id UUID NOT NULL, user_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_2A457AAC9B6B5FBA ON user_accounts (account_id)');
        $this->addSql('CREATE INDEX IDX_2A457AACA76ED395 ON user_accounts (user_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2A457AAC9B6B5FBAA76ED395 ON user_accounts (account_id, user_id)');
        $this->addSql('CREATE TABLE users (id UUID NOT NULL, telegram_id BIGINT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('ALTER TABLE user_accounts ADD CONSTRAINT FK_2A457AAC9B6B5FBA FOREIGN KEY (account_id) REFERENCES accounts (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE user_accounts ADD CONSTRAINT FK_2A457AACA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_accounts DROP CONSTRAINT FK_2A457AAC9B6B5FBA');
        $this->addSql('ALTER TABLE user_accounts DROP CONSTRAINT FK_2A457AACA76ED395');
        $this->addSql('DROP TABLE accounts');
        $this->addSql('DROP TABLE user_accounts');
        $this->addSql('DROP TABLE users');
    }
}
