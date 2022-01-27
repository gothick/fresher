<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220125181906 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->skipIf(
            !($this->connection->getDatabasePlatform() instanceof MySQLPlatform),
            "Skipping for non-MySQL database"
        );
        $this->addSql('ALTER TABLE user ADD verification_code VARCHAR(255) DEFAULT NULL, ADD verification_code_tries INT DEFAULT NULL, ADD verification_code_expires_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD phone_number_verified TINYINT(1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->skipIf(
            !($this->connection->getDatabasePlatform() instanceof MySQLPlatform),
            "Skipping for non-MySQL database"
        );
        $this->addSql('ALTER TABLE `user` DROP verification_code, DROP verification_code_tries, DROP verification_code_expires_at, DROP phone_number_verified');
    }
}
