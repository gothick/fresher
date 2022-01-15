<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220115183554 extends AbstractMigration
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
        $this->addSql('ALTER TABLE reminder_job DROP CONSTRAINT fk_b0ba8a6647c17fc7');
        $this->addSql('ALTER TABLE reminder_job DROP CONSTRAINT fk_b0ba8a6631acad7a');
        $this->addSql('DROP SEQUENCE reminder_job_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE reminder_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE theme_reminder_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE theme_reminder_job_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE theme_reminder (id INT NOT NULL, theme_id INT DEFAULT NULL, enabled BOOLEAN NOT NULL, time_of_day TIME(0) WITHOUT TIME ZONE NOT NULL, day_schedule VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E2095D3759027487 ON theme_reminder (theme_id)');
        $this->addSql('CREATE TABLE theme_reminder_job (id INT NOT NULL, theme_reminder_id INT DEFAULT NULL, scheduled_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, was_run_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_20EDBCE431ACAD7A ON theme_reminder_job (theme_reminder_id)');
        $this->addSql('COMMENT ON COLUMN theme_reminder_job.scheduled_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN theme_reminder_job.was_run_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE theme_reminder ADD CONSTRAINT FK_E2095D3759027487 FOREIGN KEY (theme_id) REFERENCES theme (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE theme_reminder_job ADD CONSTRAINT FK_20EDBCE431ACAD7A FOREIGN KEY (theme_reminder_id) REFERENCES theme_reminder (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE reminder_job');
        $this->addSql('DROP TABLE reminder');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE theme_reminder_job DROP CONSTRAINT FK_20EDBCE431ACAD7A');
        $this->addSql('DROP SEQUENCE theme_reminder_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE theme_reminder_job_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE reminder_job_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE reminder_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE reminder_job (id INT NOT NULL, goal_reminder_id INT DEFAULT NULL, theme_reminder_id INT DEFAULT NULL, scheduled_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, was_run_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, dtype VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_b0ba8a6631acad7a ON reminder_job (theme_reminder_id)');
        $this->addSql('CREATE INDEX idx_b0ba8a6647c17fc7 ON reminder_job (goal_reminder_id)');
        $this->addSql('COMMENT ON COLUMN reminder_job.scheduled_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN reminder_job.was_run_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE reminder (id INT NOT NULL, goal_id INT DEFAULT NULL, theme_id INT DEFAULT NULL, enabled BOOLEAN NOT NULL, dtype VARCHAR(255) NOT NULL, time_of_day TIME(0) WITHOUT TIME ZONE NOT NULL, day_schedule VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_40374f4059027487 ON reminder (theme_id)');
        $this->addSql('CREATE INDEX idx_40374f40667d1afe ON reminder (goal_id)');
        $this->addSql('ALTER TABLE reminder_job ADD CONSTRAINT fk_b0ba8a6647c17fc7 FOREIGN KEY (goal_reminder_id) REFERENCES reminder (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reminder_job ADD CONSTRAINT fk_b0ba8a6631acad7a FOREIGN KEY (theme_reminder_id) REFERENCES reminder (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reminder ADD CONSTRAINT fk_40374f40667d1afe FOREIGN KEY (goal_id) REFERENCES goal (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reminder ADD CONSTRAINT fk_40374f4059027487 FOREIGN KEY (theme_id) REFERENCES theme (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE theme_reminder');
        $this->addSql('DROP TABLE theme_reminder_job');
    }
}
