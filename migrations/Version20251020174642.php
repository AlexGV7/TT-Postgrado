<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251020174642 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE applicant (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, university VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE TABLE application (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, offer_id INTEGER NOT NULL, status VARCHAR(255) NOT NULL, CONSTRAINT FK_A45BDDC153C674EE FOREIGN KEY (offer_id) REFERENCES offer (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_A45BDDC153C674EE ON application (offer_id)');
        $this->addSql('CREATE TABLE offer_area (offer_id INTEGER NOT NULL, area_id INTEGER NOT NULL, PRIMARY KEY(offer_id, area_id), CONSTRAINT FK_296C137F53C674EE FOREIGN KEY (offer_id) REFERENCES offer (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_296C137FBD0F409C FOREIGN KEY (area_id) REFERENCES area (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_296C137F53C674EE ON offer_area (offer_id)');
        $this->addSql('CREATE INDEX IDX_296C137FBD0F409C ON offer_area (area_id)');
        $this->addSql('CREATE TABLE offer_keyword (offer_id INTEGER NOT NULL, keyword_id INTEGER NOT NULL, PRIMARY KEY(offer_id, keyword_id), CONSTRAINT FK_FB9BB73253C674EE FOREIGN KEY (offer_id) REFERENCES offer (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_FB9BB732115D4552 FOREIGN KEY (keyword_id) REFERENCES keyword (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_FB9BB73253C674EE ON offer_keyword (offer_id)');
        $this->addSql('CREATE INDEX IDX_FB9BB732115D4552 ON offer_keyword (keyword_id)');
        $this->addSql('CREATE TABLE offer_research_line (offer_id INTEGER NOT NULL, research_line_id INTEGER NOT NULL, PRIMARY KEY(offer_id, research_line_id), CONSTRAINT FK_D8F0D72D53C674EE FOREIGN KEY (offer_id) REFERENCES offer (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_D8F0D72DD4D5C558 FOREIGN KEY (research_line_id) REFERENCES research_line (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_D8F0D72D53C674EE ON offer_research_line (offer_id)');
        $this->addSql('CREATE INDEX IDX_D8F0D72DD4D5C558 ON offer_research_line (research_line_id)');
        $this->addSql('CREATE TABLE professor_area (professor_id INTEGER NOT NULL, area_id INTEGER NOT NULL, PRIMARY KEY(professor_id, area_id), CONSTRAINT FK_A86320B07D2D84D5 FOREIGN KEY (professor_id) REFERENCES professor (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_A86320B0BD0F409C FOREIGN KEY (area_id) REFERENCES area (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_A86320B07D2D84D5 ON professor_area (professor_id)');
        $this->addSql('CREATE INDEX IDX_A86320B0BD0F409C ON professor_area (area_id)');
        $this->addSql('CREATE TABLE professor_keyword (professor_id INTEGER NOT NULL, keyword_id INTEGER NOT NULL, PRIMARY KEY(professor_id, keyword_id), CONSTRAINT FK_5BC1E6F7D2D84D5 FOREIGN KEY (professor_id) REFERENCES professor (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5BC1E6F115D4552 FOREIGN KEY (keyword_id) REFERENCES keyword (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_5BC1E6F7D2D84D5 ON professor_keyword (professor_id)');
        $this->addSql('CREATE INDEX IDX_5BC1E6F115D4552 ON professor_keyword (keyword_id)');
        $this->addSql('CREATE TABLE professor_research_line (professor_id INTEGER NOT NULL, research_line_id INTEGER NOT NULL, PRIMARY KEY(professor_id, research_line_id), CONSTRAINT FK_9768F8BE7D2D84D5 FOREIGN KEY (professor_id) REFERENCES professor (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_9768F8BED4D5C558 FOREIGN KEY (research_line_id) REFERENCES research_line (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_9768F8BE7D2D84D5 ON professor_research_line (professor_id)');
        $this->addSql('CREATE INDEX IDX_9768F8BED4D5C558 ON professor_research_line (research_line_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE applicant');
        $this->addSql('DROP TABLE application');
        $this->addSql('DROP TABLE offer_area');
        $this->addSql('DROP TABLE offer_keyword');
        $this->addSql('DROP TABLE offer_research_line');
        $this->addSql('DROP TABLE professor_area');
        $this->addSql('DROP TABLE professor_keyword');
        $this->addSql('DROP TABLE professor_research_line');
    }
}
