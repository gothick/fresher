<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220112193149 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->skipIf(!($this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform), "Skipping for non-Postgres database");
        $this->addSql('CREATE SEQUENCE reminder_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE reminder (id INT NOT NULL, goal_id INT DEFAULT NULL, theme_id INT DEFAULT NULL, enabled BOOLEAN NOT NULL, dtype VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_40374F40667D1AFE ON reminder (goal_id)');
        $this->addSql('CREATE INDEX IDX_40374F4059027487 ON reminder (theme_id)');
        $this->addSql('ALTER TABLE reminder ADD CONSTRAINT FK_40374F40667D1AFE FOREIGN KEY (goal_id) REFERENCES goal (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reminder ADD CONSTRAINT FK_40374F4059027487 FOREIGN KEY (theme_id) REFERENCES theme (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE reminder_id_seq CASCADE');
        $this->addSql('DROP TABLE reminder');
    }
}
