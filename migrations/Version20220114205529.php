<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220114205529 extends AbstractMigration
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
        $this->addSql('CREATE TABLE reminder_job (id INT AUTO_INCREMENT NOT NULL, goal_reminder_id INT DEFAULT NULL, theme_reminder_id INT DEFAULT NULL, scheduled_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', was_run_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', dtype VARCHAR(255) NOT NULL, INDEX IDX_B0BA8A6647C17FC7 (goal_reminder_id), INDEX IDX_B0BA8A6631ACAD7A (theme_reminder_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reminder_job ADD CONSTRAINT FK_B0BA8A6647C17FC7 FOREIGN KEY (goal_reminder_id) REFERENCES reminder (id)');
        $this->addSql('ALTER TABLE reminder_job ADD CONSTRAINT FK_B0BA8A6631ACAD7A FOREIGN KEY (theme_reminder_id) REFERENCES reminder (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE reminder_job');
    }
}
