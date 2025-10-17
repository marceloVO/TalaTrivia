<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251016150951 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE participation RENAME INDEX idx_18ec0266e8de6e08 TO IDX_AB55E24FE8DE6E08');
        $this->addSql('ALTER TABLE participation RENAME INDEX idx_18ec0266a76ed395 TO IDX_AB55E24FA76ED395');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE participation RENAME INDEX idx_ab55e24fa76ed395 TO IDX_18EC0266A76ED395');
        $this->addSql('ALTER TABLE participation RENAME INDEX idx_ab55e24fe8de6e08 TO IDX_18EC0266E8DE6E08');
    }
}
