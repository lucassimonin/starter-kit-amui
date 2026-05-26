<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Per-page share / crawl hints (canonical, OG, robots, twitter card…).
 */
final class Version20260529120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds Page SEO columns: robots, canonical override, OG type/site name, Twitter card.';
    }

    public function up(Schema $schema): void
    {
        // MySQL / MariaDB syntax (consistent with sibling migrations).
        $this->addSql('ALTER TABLE page ADD meta_robots VARCHAR(120) DEFAULT NULL');
        $this->addSql('ALTER TABLE page ADD canonical_override VARCHAR(500) DEFAULT NULL');
        $this->addSql('ALTER TABLE page ADD og_type VARCHAR(32) NOT NULL DEFAULT \'website\'');
        $this->addSql('ALTER TABLE page ADD og_site_name VARCHAR(80) DEFAULT NULL');
        $this->addSql('ALTER TABLE page ADD twitter_card VARCHAR(32) NOT NULL DEFAULT \'summary_large_image\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE page DROP COLUMN meta_robots');
        $this->addSql('ALTER TABLE page DROP COLUMN canonical_override');
        $this->addSql('ALTER TABLE page DROP COLUMN og_type');
        $this->addSql('ALTER TABLE page DROP COLUMN og_site_name');
        $this->addSql('ALTER TABLE page DROP COLUMN twitter_card');
    }
}
