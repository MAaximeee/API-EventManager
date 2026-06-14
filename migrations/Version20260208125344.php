<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260208125344 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE team (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, color VARCHAR(7) DEFAULT NULL, max_size INT DEFAULT NULL, created_at DATETIME NOT NULL, event_id INT NOT NULL, INDEX IDX_C4E0A61F71F7E88B (event_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE team_member (id INT AUTO_INCREMENT NOT NULL, role VARCHAR(20) NOT NULL, joined_at DATETIME NOT NULL, team_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_6FFBDA1296CD8AE (team_id), INDEX IDX_6FFBDA1A76ED395 (user_id), UNIQUE INDEX UNIQ_TEAM_USER (team_id, user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61F71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE team_member ADD CONSTRAINT FK_6FFBDA1296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE team_member ADD CONSTRAINT FK_6FFBDA1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE event ADD has_teams TINYINT DEFAULT 0 NOT NULL, ADD team_count INT DEFAULT NULL, ADD allow_participant_create_team TINYINT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61F71F7E88B');
        $this->addSql('ALTER TABLE team_member DROP FOREIGN KEY FK_6FFBDA1296CD8AE');
        $this->addSql('ALTER TABLE team_member DROP FOREIGN KEY FK_6FFBDA1A76ED395');
        $this->addSql('DROP TABLE team');
        $this->addSql('DROP TABLE team_member');
        $this->addSql('ALTER TABLE event DROP has_teams, DROP team_count, DROP allow_participant_create_team');
    }
}
