<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260611151619 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681EF135FD3D FOREIGN KEY (image_devenement_id) REFERENCES image_devenement (id)');
        $this->addSql('ALTER TABLE info_du_prix ADD CONSTRAINT FK_57576F3C4ADA8B82 FOREIGN KEY (id_prix_id) REFERENCES prix (id)');
        $this->addSql('ALTER TABLE prix ADD formule_contact VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY FK_B26681EF135FD3D');
        $this->addSql('ALTER TABLE info_du_prix DROP FOREIGN KEY FK_57576F3C4ADA8B82');
        $this->addSql('ALTER TABLE prix DROP formule_contact');
    }
}
