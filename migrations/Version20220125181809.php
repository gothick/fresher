<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220125181809 extends AbstractMigration
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
        $this->addSql('ALTER TABLE "user" ADD verification_code VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD verification_code_tries INT DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD verification_code_expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD phone_number_verified BOOLEAN DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN "user".verification_code_expires_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->skipIf(
            !($this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform),
            "Skipping for non-Postgres database"
        );
        $this->addSql('ALTER TABLE "user" DROP verification_code');
        $this->addSql('ALTER TABLE "user" DROP verification_code_tries');
        $this->addSql('ALTER TABLE "user" DROP verification_code_expires_at');
        $this->addSql('ALTER TABLE "user" DROP phone_number_verified');
    }
}
