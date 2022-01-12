<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Controller\GoalController;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220112193409 extends AbstractMigration
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
        $this->addSql('CREATE TABLE reminder (id INT AUTO_INCREMENT NOT NULL, goal_id INT DEFAULT NULL, theme_id INT DEFAULT NULL, enabled TINYINT(1) NOT NULL, dtype VARCHAR(255) NOT NULL, INDEX IDX_40374F40667D1AFE (goal_id), INDEX IDX_40374F4059027487 (theme_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reminder ADD CONSTRAINT FK_40374F40667D1AFE FOREIGN KEY (goal_id) REFERENCES goal (id)');
        $this->addSql('ALTER TABLE reminder ADD CONSTRAINT FK_40374F4059027487 FOREIGN KEY (theme_id) REFERENCES theme (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE reminder');
    }
}
