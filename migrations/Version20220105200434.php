<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220105200434 extends AbstractMigration
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
        $this->addSql('CREATE SEQUENCE goal_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE goal (id INT NOT NULL, theme_id INT NOT NULL, name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, reason TEXT DEFAULT NULL, start_date DATE DEFAULT NULL, end_date DATE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FCDCEB2E59027487 ON goal (theme_id)');
        $this->addSql('ALTER TABLE goal ADD CONSTRAINT FK_FCDCEB2E59027487 FOREIGN KEY (theme_id) REFERENCES theme (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE goal_id_seq CASCADE');
        $this->addSql('DROP TABLE goal');
    }
}
