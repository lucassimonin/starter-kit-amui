<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260526083227 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contact_message (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(120) NOT NULL, email VARCHAR(180) NOT NULL, phone VARCHAR(30) DEFAULT NULL, subject VARCHAR(180) DEFAULT NULL, message LONGTEXT NOT NULL, ip_address VARCHAR(45) DEFAULT NULL, user_agent VARCHAR(255) DEFAULT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, INDEX idx_contact_status (status), INDEX idx_contact_created (created_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE page (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(150) NOT NULL, slug VARCHAR(160) NOT NULL, meta_title VARCHAR(70) DEFAULT NULL, meta_description VARCHAR(180) DEFAULT NULL, og_image VARCHAR(255) DEFAULT NULL, is_homepage TINYINT NOT NULL, is_published TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_140AB620989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE section_block (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(100) NOT NULL, layout VARCHAR(30) NOT NULL, payload JSON NOT NULL, position INT NOT NULL, is_enabled TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, page_id INT NOT NULL, INDEX IDX_144CF046C4663E4 (page_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE section_block ADD CONSTRAINT FK_144CF046C4663E4 FOREIGN KEY (page_id) REFERENCES page (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE section_block DROP FOREIGN KEY FK_144CF046C4663E4');
        $this->addSql('DROP TABLE contact_message');
        $this->addSql('DROP TABLE page');
        $this->addSql('DROP TABLE section_block');
    }
}
