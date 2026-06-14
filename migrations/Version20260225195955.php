<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260225195955 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE requests (id INT AUTO_INCREMENT NOT NULL, objet VARCHAR(255) NOT NULL, message LONGTEXT NOT NULL, status VARCHAR(20) NOT NULL, creator_id INT NOT NULL, INDEX IDX_7B85D65161220EA6 (creator_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE resultat_course (id INT AUTO_INCREMENT NOT NULL, temps INT DEFAULT NULL, place INT DEFAULT NULL, status VARCHAR(20) NOT NULL, event_id INT NOT NULL, participant_id INT NOT NULL, INDEX IDX_7E0F13E471F7E88B (event_id), INDEX IDX_7E0F13E49D1C3019 (participant_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE score_match (id INT AUTO_INCREMENT NOT NULL, score_team_a INT DEFAULT NULL, status VARCHAR(20) NOT NULL, score_team_b INT DEFAULT NULL, event_id INT NOT NULL, team_a_id INT DEFAULT NULL, team_b_id INT DEFAULT NULL, INDEX IDX_C317098D71F7E88B (event_id), INDEX IDX_C317098DEA3FA723 (team_a_id), INDEX IDX_C317098DF88A08CD (team_b_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE requests ADD CONSTRAINT FK_7B85D65161220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE resultat_course ADD CONSTRAINT FK_7E0F13E471F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE resultat_course ADD CONSTRAINT FK_7E0F13E49D1C3019 FOREIGN KEY (participant_id) REFERENCES event_participant (id)');
        $this->addSql('ALTER TABLE score_match ADD CONSTRAINT FK_C317098D71F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE score_match ADD CONSTRAINT FK_C317098DEA3FA723 FOREIGN KEY (team_a_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE score_match ADD CONSTRAINT FK_C317098DF88A08CD FOREIGN KEY (team_b_id) REFERENCES team (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE requests DROP FOREIGN KEY FK_7B85D65161220EA6');
        $this->addSql('ALTER TABLE resultat_course DROP FOREIGN KEY FK_7E0F13E471F7E88B');
        $this->addSql('ALTER TABLE resultat_course DROP FOREIGN KEY FK_7E0F13E49D1C3019');
        $this->addSql('ALTER TABLE score_match DROP FOREIGN KEY FK_C317098D71F7E88B');
        $this->addSql('ALTER TABLE score_match DROP FOREIGN KEY FK_C317098DEA3FA723');
        $this->addSql('ALTER TABLE score_match DROP FOREIGN KEY FK_C317098DF88A08CD');
        $this->addSql('DROP TABLE requests');
        $this->addSql('DROP TABLE resultat_course');
        $this->addSql('DROP TABLE score_match');
    }
}
