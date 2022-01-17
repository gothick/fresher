<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220117172933 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->skipIf(
            !($this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform),
            "Skipping for non-Postgres database"
        );
        $this->addSql('ALTER TABLE theme_reminder ADD reminder_type VARCHAR(255) DEFAULT NULL');
        $this->addSql("UPDATE theme_reminder SET reminder_type = 'email'");
        $this->addSql('ALTER TABLE theme_reminder ALTER COLUMN reminder_type SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE theme_reminder DROP reminder_type');
    }
}
