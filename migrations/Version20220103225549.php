<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220103225549 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Version20220105235115 rolls this migration up along with others for MySQL; we only
        // want to run this if we're on Postgres.
        $this->skipIf(!($this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform));
        $this->addSql('ALTER TABLE "user" ADD is_verified BOOLEAN NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "user" DROP is_verified');
    }
}
