<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220115190642 extends AbstractMigration
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
        $this->addSql('ALTER TABLE reminder_job DROP FOREIGN KEY FK_B0BA8A6631ACAD7A');
        $this->addSql('ALTER TABLE reminder_job DROP FOREIGN KEY FK_B0BA8A6647C17FC7');
        $this->addSql('CREATE TABLE theme_reminder (id INT AUTO_INCREMENT NOT NULL, theme_id INT DEFAULT NULL, enabled TINYINT(1) NOT NULL, time_of_day TIME NOT NULL, day_schedule VARCHAR(255) NOT NULL, INDEX IDX_E2095D3759027487 (theme_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE theme_reminder_job (id INT AUTO_INCREMENT NOT NULL, theme_reminder_id INT DEFAULT NULL, scheduled_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', was_run_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_20EDBCE431ACAD7A (theme_reminder_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE theme_reminder ADD CONSTRAINT FK_E2095D3759027487 FOREIGN KEY (theme_id) REFERENCES theme (id)');
        $this->addSql('ALTER TABLE theme_reminder_job ADD CONSTRAINT FK_20EDBCE431ACAD7A FOREIGN KEY (theme_reminder_id) REFERENCES theme_reminder (id)');
        $this->addSql('DROP TABLE reminder');
        $this->addSql('DROP TABLE reminder_job');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE theme_reminder_job DROP FOREIGN KEY FK_20EDBCE431ACAD7A');
        $this->addSql('CREATE TABLE reminder (id INT AUTO_INCREMENT NOT NULL, goal_id INT DEFAULT NULL, theme_id INT DEFAULT NULL, enabled TINYINT(1) NOT NULL, dtype VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, time_of_day TIME NOT NULL, day_schedule VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_40374F4059027487 (theme_id), INDEX IDX_40374F40667D1AFE (goal_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE reminder_job (id INT AUTO_INCREMENT NOT NULL, goal_reminder_id INT DEFAULT NULL, theme_reminder_id INT DEFAULT NULL, scheduled_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', was_run_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', dtype VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_B0BA8A6631ACAD7A (theme_reminder_id), INDEX IDX_B0BA8A6647C17FC7 (goal_reminder_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE reminder ADD CONSTRAINT FK_40374F4059027487 FOREIGN KEY (theme_id) REFERENCES theme (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE reminder ADD CONSTRAINT FK_40374F40667D1AFE FOREIGN KEY (goal_id) REFERENCES goal (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE reminder_job ADD CONSTRAINT FK_B0BA8A6631ACAD7A FOREIGN KEY (theme_reminder_id) REFERENCES reminder (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE reminder_job ADD CONSTRAINT FK_B0BA8A6647C17FC7 FOREIGN KEY (goal_reminder_id) REFERENCES reminder (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('DROP TABLE theme_reminder');
        $this->addSql('DROP TABLE theme_reminder_job');
    }
}
