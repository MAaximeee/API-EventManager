<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260213141021 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE event_participant (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(20) NOT NULL, registered_at DATETIME NOT NULL, event_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_7C16B89171F7E88B (event_id), INDEX IDX_7C16B891A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE team (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, color VARCHAR(7) DEFAULT NULL, max_size INT DEFAULT NULL, created_at DATETIME NOT NULL, event_id INT NOT NULL, INDEX IDX_C4E0A61F71F7E88B (event_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE team_member (id INT AUTO_INCREMENT NOT NULL, role VARCHAR(20) NOT NULL, joined_at DATETIME NOT NULL, team_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_6FFBDA1296CD8AE (team_id), INDEX IDX_6FFBDA1A76ED395 (user_id), UNIQUE INDEX UNIQ_TEAM_USER (team_id, user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE event_participant ADD CONSTRAINT FK_7C16B89171F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE event_participant ADD CONSTRAINT FK_7C16B891A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61F71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE team_member ADD CONSTRAINT FK_6FFBDA1296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE team_member ADD CONSTRAINT FK_6FFBDA1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE event ADD has_teams TINYINT DEFAULT 0 NOT NULL, ADD allow_participant_create_team TINYINT DEFAULT 0 NOT NULL, ADD creator_id INT NOT NULL');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA761220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA761220EA6 ON event (creator_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event_participant DROP FOREIGN KEY FK_7C16B89171F7E88B');
        $this->addSql('ALTER TABLE event_participant DROP FOREIGN KEY FK_7C16B891A76ED395');
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61F71F7E88B');
        $this->addSql('ALTER TABLE team_member DROP FOREIGN KEY FK_6FFBDA1296CD8AE');
        $this->addSql('ALTER TABLE team_member DROP FOREIGN KEY FK_6FFBDA1A76ED395');
        $this->addSql('DROP TABLE event_participant');
        $this->addSql('DROP TABLE team');
        $this->addSql('DROP TABLE team_member');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA761220EA6');
        $this->addSql('DROP INDEX IDX_3BAE0AA761220EA6 ON event');
        $this->addSql('ALTER TABLE event DROP has_teams, DROP allow_participant_create_team, DROP creator_id');
    }
}
