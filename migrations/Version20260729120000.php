<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajoute une colonne image (nom de fichier) aux actualités (table evenement).
 */
final class Version20260729120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de la colonne image sur evenement (actualités)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE evenement ADD image VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE evenement DROP image');
    }
}
