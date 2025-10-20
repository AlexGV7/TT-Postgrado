<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251020172525 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE area (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE TABLE department (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, yes CLOB NOT NULL)');
        $this->addSql('CREATE TABLE keyword (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, word VARCHAR(255) NOT NULL, description CLOB NOT NULL)');
        $this->addSql('CREATE TABLE offer (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, professor_id INTEGER NOT NULL, title VARCHAR(255) NOT NULL, body CLOB DEFAULT NULL, CONSTRAINT FK_29D6873E7D2D84D5 FOREIGN KEY (professor_id) REFERENCES professor (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_29D6873E7D2D84D5 ON offer (professor_id)');
        $this->addSql('CREATE TABLE professor (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, department_id INTEGER NOT NULL, email VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, image BLOB DEFAULT NULL, CONSTRAINT FK_790DD7E3AE80F5DF FOREIGN KEY (department_id) REFERENCES department (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_790DD7E3AE80F5DF ON professor (department_id)');
        $this->addSql('CREATE TABLE research_line (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description CLOB NOT NULL)');
        $this->addSql('CREATE TABLE "user" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, deactivated_notifications BOOLEAN NOT NULL, sex BOOLEAN NOT NULL, rut INTEGER NOT NULL, name VARCHAR(30) NOT NULL, last_name VARCHAR(20) NOT NULL, phone VARCHAR(30) DEFAULT NULL, picture VARCHAR(255) DEFAULT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON "user" (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_RUT ON "user" (rut)');
        $this->addSql('CREATE TABLE messenger_messages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, body CLOB NOT NULL, headers CLOB NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , available_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , delivered_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        )');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE area');
        $this->addSql('DROP TABLE department');
        $this->addSql('DROP TABLE keyword');
        $this->addSql('DROP TABLE offer');
        $this->addSql('DROP TABLE professor');
        $this->addSql('DROP TABLE research_line');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
