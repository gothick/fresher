<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220114205218 extends AbstractMigration
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
        $this->addSql('CREATE SEQUENCE reminder_job_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE reminder_job (id INT NOT NULL, goal_reminder_id INT DEFAULT NULL, theme_reminder_id INT DEFAULT NULL, scheduled_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, was_run_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, dtype VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B0BA8A6647C17FC7 ON reminder_job (goal_reminder_id)');
        $this->addSql('CREATE INDEX IDX_B0BA8A6631ACAD7A ON reminder_job (theme_reminder_id)');
        $this->addSql('COMMENT ON COLUMN reminder_job.scheduled_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN reminder_job.was_run_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE reminder_job ADD CONSTRAINT FK_B0BA8A6647C17FC7 FOREIGN KEY (goal_reminder_id) REFERENCES reminder (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reminder_job ADD CONSTRAINT FK_B0BA8A6631ACAD7A FOREIGN KEY (theme_reminder_id) REFERENCES reminder (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE reminder_job_id_seq CASCADE');
        $this->addSql('DROP TABLE reminder_job');
    }
}
