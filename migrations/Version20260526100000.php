<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260526100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Page.footer_payload and SectionBlock.anchor_id for public starter layout.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE page ADD footer_payload JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE section_block ADD anchor_id VARCHAR(80) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE page DROP footer_payload');
        $this->addSql('ALTER TABLE section_block DROP anchor_id');
    }
}
