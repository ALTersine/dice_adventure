<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260611131823 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681EF135FD3D FOREIGN KEY (image_devenement_id) REFERENCES image_devenement (id)');
        $this->addSql('ALTER TABLE prix ADD mettre_en_avant TINYINT NOT NULL');
        $this->addSql('ALTER TABLE prix ADD CONSTRAINT FK_F7EFEA5E5C5A6AAC FOREIGN KEY (info_du_prix_id) REFERENCES info_du_prix (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY FK_B26681EF135FD3D');
        $this->addSql('ALTER TABLE prix DROP FOREIGN KEY FK_F7EFEA5E5C5A6AAC');
        $this->addSql('ALTER TABLE prix DROP mettre_en_avant');
    }
}
