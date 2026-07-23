<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration.
 */
final class Version20260723203254 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE accounts (id UUID NOT NULL, name VARCHAR(100) NOT NULL, type VARCHAR(8) NOT NULL, created_at DATE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE categories (id UUID NOT NULL, type VARCHAR(7) NOT NULL, name VARCHAR(30) NOT NULL, account_id UUID NOT NULL, created_by UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_3AF346689B6B5FBA ON categories (account_id)');
        $this->addSql('CREATE INDEX IDX_3AF34668DE12AB56 ON categories (created_by)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3AF346689B6B5FBA5E237E068CDE5729 ON categories (account_id, name, type)');
        $this->addSql('CREATE TABLE transactions (id UUID NOT NULL, description VARCHAR(255) NOT NULL, date DATE NOT NULL, created_at DATE NOT NULL, money_amount NUMERIC(10, 2) NOT NULL, money_currency VARCHAR(3) NOT NULL, account_id UUID NOT NULL, category_id UUID NOT NULL, created_by UUID NOT NULL, updated_by UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_EAA81A4C9B6B5FBA ON transactions (account_id)');
        $this->addSql('CREATE INDEX IDX_EAA81A4C12469DE2 ON transactions (category_id)');
        $this->addSql('CREATE INDEX IDX_EAA81A4CDE12AB56 ON transactions (created_by)');
        $this->addSql('CREATE INDEX IDX_EAA81A4C16FE72E1 ON transactions (updated_by)');
        $this->addSql('CREATE TABLE user_accounts (id UUID NOT NULL, joined_at DATE NOT NULL, account_id UUID NOT NULL, user_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_2A457AAC9B6B5FBA ON user_accounts (account_id)');
        $this->addSql('CREATE INDEX IDX_2A457AACA76ED395 ON user_accounts (user_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2A457AAC9B6B5FBAA76ED395 ON user_accounts (account_id, user_id)');
        $this->addSql('CREATE TABLE users (id UUID NOT NULL, telegram_id BIGINT DEFAULT NULL, locale VARCHAR(2) NOT NULL, created_at DATE NOT NULL, reminder_reminders_enabled BOOLEAN NOT NULL, reminder_reminder_time VARCHAR(5) NOT NULL, reminder_timezone VARCHAR(64) NOT NULL, reminder_last_reminder_on DATE DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9CC0B3066 ON users (telegram_id)');
        $this->addSql('ALTER TABLE categories ADD CONSTRAINT FK_3AF346689B6B5FBA FOREIGN KEY (account_id) REFERENCES accounts (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE categories ADD CONSTRAINT FK_3AF34668DE12AB56 FOREIGN KEY (created_by) REFERENCES users (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE transactions ADD CONSTRAINT FK_EAA81A4C9B6B5FBA FOREIGN KEY (account_id) REFERENCES accounts (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE transactions ADD CONSTRAINT FK_EAA81A4C12469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE transactions ADD CONSTRAINT FK_EAA81A4CDE12AB56 FOREIGN KEY (created_by) REFERENCES users (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE transactions ADD CONSTRAINT FK_EAA81A4C16FE72E1 FOREIGN KEY (updated_by) REFERENCES users (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE user_accounts ADD CONSTRAINT FK_2A457AAC9B6B5FBA FOREIGN KEY (account_id) REFERENCES accounts (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE user_accounts ADD CONSTRAINT FK_2A457AACA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE categories DROP CONSTRAINT FK_3AF346689B6B5FBA');
        $this->addSql('ALTER TABLE categories DROP CONSTRAINT FK_3AF34668DE12AB56');
        $this->addSql('ALTER TABLE transactions DROP CONSTRAINT FK_EAA81A4C9B6B5FBA');
        $this->addSql('ALTER TABLE transactions DROP CONSTRAINT FK_EAA81A4C12469DE2');
        $this->addSql('ALTER TABLE transactions DROP CONSTRAINT FK_EAA81A4CDE12AB56');
        $this->addSql('ALTER TABLE transactions DROP CONSTRAINT FK_EAA81A4C16FE72E1');
        $this->addSql('ALTER TABLE user_accounts DROP CONSTRAINT FK_2A457AAC9B6B5FBA');
        $this->addSql('ALTER TABLE user_accounts DROP CONSTRAINT FK_2A457AACA76ED395');
        $this->addSql('DROP TABLE accounts');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP TABLE transactions');
        $this->addSql('DROP TABLE user_accounts');
        $this->addSql('DROP TABLE users');
    }
}
