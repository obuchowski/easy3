<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201108220344 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE resource_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE resource (id INT NOT NULL, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BC91F416A76ED395 ON resource (user_id)');
        $this->addSql('ALTER TABLE resource ADD CONSTRAINT FK_BC91F416A76ED395 FOREIGN KEY (user_id) REFERENCES user_table (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE store DROP CONSTRAINT fk_ff575877a76ed395');
        $this->addSql('DROP INDEX idx_ff575877a76ed395');
        $this->addSql('ALTER TABLE store RENAME COLUMN user_id TO resource_id');
        $this->addSql('ALTER TABLE store ADD CONSTRAINT FK_FF57587789329D25 FOREIGN KEY (resource_id) REFERENCES resource (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_FF57587789329D25 ON store (resource_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE store DROP CONSTRAINT FK_FF57587789329D25');
        $this->addSql('DROP SEQUENCE resource_id_seq CASCADE');
        $this->addSql('DROP TABLE resource');
        $this->addSql('DROP INDEX IDX_FF57587789329D25');
        $this->addSql('ALTER TABLE store RENAME COLUMN resource_id TO user_id');
        $this->addSql('ALTER TABLE store ADD CONSTRAINT fk_ff575877a76ed395 FOREIGN KEY (user_id) REFERENCES user_table (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_ff575877a76ed395 ON store (user_id)');
    }
}
