<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260127164349 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE usuario ADD nombre VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE usuario ADD apellidos VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE usuario DROP nombre_usuario');
        $this->addSql('ALTER TABLE usuario ALTER email TYPE VARCHAR(180)');
        $this->addSql('ALTER TABLE usuario ALTER password TYPE VARCHAR(255)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2265B05DE7927C74 ON usuario (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_2265B05DE7927C74');
        $this->addSql('ALTER TABLE usuario ADD nombre_usuario VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE usuario DROP nombre');
        $this->addSql('ALTER TABLE usuario DROP apellidos');
        $this->addSql('ALTER TABLE usuario ALTER email TYPE VARCHAR(500)');
        $this->addSql('ALTER TABLE usuario ALTER password TYPE VARCHAR(500)');
    }
}
