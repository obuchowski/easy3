<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201101202725 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE feed_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE feed (id INT NOT NULL, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, enabled BOOLEAN NOT NULL, header TEXT NOT NULL, item VARCHAR(255) NOT NULL, content TEXT NOT NULL, footer TEXT NOT NULL, currency VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_234044ABA76ED395 ON feed (user_id)');
        $this->addSql('ALTER TABLE feed ADD CONSTRAINT FK_234044ABA76ED395 FOREIGN KEY (user_id) REFERENCES user_table (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE feed_id_seq CASCADE');
        $this->addSql('DROP TABLE feed');
    }
}
