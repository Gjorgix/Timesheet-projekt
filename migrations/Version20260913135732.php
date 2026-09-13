<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260913135732 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE time_entry ADD start_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE time_entry ADD end_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE time_entry DROP date');
        $this->addSql('ALTER TABLE time_entry DROP duration');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE time_entry ADD date DATE NOT NULL');
        $this->addSql('ALTER TABLE time_entry ADD duration INT NOT NULL');
        $this->addSql('ALTER TABLE time_entry DROP start_at');
        $this->addSql('ALTER TABLE time_entry DROP end_at');
    }
}
