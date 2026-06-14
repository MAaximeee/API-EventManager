<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260206104659 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, status VARCHAR(20) NOT NULL, type VARCHAR(20) NOT NULL, visibility VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, due_date DATE DEFAULT NULL, creator_id INT NOT NULL, INDEX IDX_3BAE0AA761220EA6 (creator_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, username VARCHAR(100) DEFAULT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA761220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA761220EA6');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE user');
    }
}
