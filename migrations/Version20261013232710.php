<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261013232710 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE project ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE project ADD created_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE project ADD updated_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EEB03A8386 FOREIGN KEY (created_by_id) REFERENCES "user" (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE896DBBDE FOREIGN KEY (updated_by_id) REFERENCES "user" (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_2FB3D0EEB03A8386 ON project (created_by_id)');
        $this->addSql('CREATE INDEX IDX_2FB3D0EE896DBBDE ON project (updated_by_id)');
        $this->addSql('ALTER TABLE time_entry ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE time_entry ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE time_entry ADD created_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE time_entry ADD updated_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE time_entry ADD CONSTRAINT FK_6E537C0CB03A8386 FOREIGN KEY (created_by_id) REFERENCES "user" (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE time_entry ADD CONSTRAINT FK_6E537C0C896DBBDE FOREIGN KEY (updated_by_id) REFERENCES "user" (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_6E537C0CB03A8386 ON time_entry (created_by_id)');
        $this->addSql('CREATE INDEX IDX_6E537C0C896DBBDE ON time_entry (updated_by_id)');
        $this->addSql('ALTER TABLE "user" ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD created_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD updated_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649B03A8386 FOREIGN KEY (created_by_id) REFERENCES "user" (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649896DBBDE FOREIGN KEY (updated_by_id) REFERENCES "user" (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_8D93D649B03A8386 ON "user" (created_by_id)');
        $this->addSql('CREATE INDEX IDX_8D93D649896DBBDE ON "user" (updated_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project DROP CONSTRAINT FK_2FB3D0EEB03A8386');
        $this->addSql('ALTER TABLE project DROP CONSTRAINT FK_2FB3D0EE896DBBDE');
        $this->addSql('DROP INDEX IDX_2FB3D0EEB03A8386');
        $this->addSql('DROP INDEX IDX_2FB3D0EE896DBBDE');
        $this->addSql('ALTER TABLE project DROP created_at');
        $this->addSql('ALTER TABLE project DROP updated_at');
        $this->addSql('ALTER TABLE project DROP created_by_id');
        $this->addSql('ALTER TABLE project DROP updated_by_id');
        $this->addSql('ALTER TABLE time_entry DROP CONSTRAINT FK_6E537C0CB03A8386');
        $this->addSql('ALTER TABLE time_entry DROP CONSTRAINT FK_6E537C0C896DBBDE');
        $this->addSql('DROP INDEX IDX_6E537C0CB03A8386');
        $this->addSql('DROP INDEX IDX_6E537C0C896DBBDE');
        $this->addSql('ALTER TABLE time_entry DROP created_at');
        $this->addSql('ALTER TABLE time_entry DROP updated_at');
        $this->addSql('ALTER TABLE time_entry DROP created_by_id');
        $this->addSql('ALTER TABLE time_entry DROP updated_by_id');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649B03A8386');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649896DBBDE');
        $this->addSql('DROP INDEX IDX_8D93D649B03A8386');
        $this->addSql('DROP INDEX IDX_8D93D649896DBBDE');
        $this->addSql('ALTER TABLE "user" DROP created_at');
        $this->addSql('ALTER TABLE "user" DROP updated_at');
        $this->addSql('ALTER TABLE "user" DROP created_by_id');
        $this->addSql('ALTER TABLE "user" DROP updated_by_id');
    }
}
