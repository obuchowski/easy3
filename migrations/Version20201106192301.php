<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201106192301 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product DROP CONSTRAINT fk_d34a04ada76ed395');
        $this->addSql('DROP INDEX idx_d34a04ada76ed395');
        $this->addSql('ALTER TABLE product RENAME COLUMN user_id TO store_id');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADB092A811 FOREIGN KEY (store_id) REFERENCES store (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_D34A04ADB092A811 ON product (store_id)');
        $this->addSql('ALTER TABLE product_option DROP CONSTRAINT fk_38fa4114a76ed395');
        $this->addSql('DROP INDEX idx_38fa4114a76ed395');
        $this->addSql('ALTER TABLE product_option RENAME COLUMN user_id TO store_id');
        $this->addSql('ALTER TABLE product_option ADD CONSTRAINT FK_38FA4114B092A811 FOREIGN KEY (store_id) REFERENCES store (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_38FA4114B092A811 ON product_option (store_id)');
        $this->addSql('ALTER TABLE store ADD original_id INT NOT NULL');
        $this->addSql('ALTER TABLE store ADD code VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE store DROP original_id');
        $this->addSql('ALTER TABLE store DROP code');
        $this->addSql('ALTER TABLE product DROP CONSTRAINT FK_D34A04ADB092A811');
        $this->addSql('DROP INDEX IDX_D34A04ADB092A811');
        $this->addSql('ALTER TABLE product RENAME COLUMN store_id TO user_id');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT fk_d34a04ada76ed395 FOREIGN KEY (user_id) REFERENCES user_table (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_d34a04ada76ed395 ON product (user_id)');
        $this->addSql('ALTER TABLE product_option DROP CONSTRAINT FK_38FA4114B092A811');
        $this->addSql('DROP INDEX IDX_38FA4114B092A811');
        $this->addSql('ALTER TABLE product_option RENAME COLUMN store_id TO user_id');
        $this->addSql('ALTER TABLE product_option ADD CONSTRAINT fk_38fa4114a76ed395 FOREIGN KEY (user_id) REFERENCES user_table (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_38fa4114a76ed395 ON product_option (user_id)');
    }
}
