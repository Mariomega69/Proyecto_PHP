<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260203155541 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ranking_valoracion (ranking_id INT NOT NULL, valoracion_id INT NOT NULL, PRIMARY KEY (ranking_id, valoracion_id))');
        $this->addSql('CREATE INDEX IDX_3548B69A20F64684 ON ranking_valoracion (ranking_id)');
        $this->addSql('CREATE INDEX IDX_3548B69AD29AA1AC ON ranking_valoracion (valoracion_id)');
        $this->addSql('ALTER TABLE ranking_valoracion ADD CONSTRAINT FK_3548B69A20F64684 FOREIGN KEY (ranking_id) REFERENCES ranking (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ranking_valoracion ADD CONSTRAINT FK_3548B69AD29AA1AC FOREIGN KEY (valoracion_id) REFERENCES valoracion (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ranking ADD nombre VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ranking_valoracion DROP CONSTRAINT FK_3548B69A20F64684');
        $this->addSql('ALTER TABLE ranking_valoracion DROP CONSTRAINT FK_3548B69AD29AA1AC');
        $this->addSql('DROP TABLE ranking_valoracion');
        $this->addSql('ALTER TABLE ranking DROP nombre');
    }
}
