<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260611140810 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE avis (id INT AUTO_INCREMENT NOT NULL, message VARCHAR(255) NOT NULL, auteur VARCHAR(255) NOT NULL, note INT NOT NULL, infos_supp_auteur VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681EF135FD3D FOREIGN KEY (image_devenement_id) REFERENCES image_devenement (id)');
        $this->addSql('ALTER TABLE info_du_prix ADD id_prix_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE info_du_prix ADD CONSTRAINT FK_57576F3C4ADA8B82 FOREIGN KEY (id_prix_id) REFERENCES prix (id)');
        $this->addSql('CREATE INDEX IDX_57576F3C4ADA8B82 ON info_du_prix (id_prix_id)');
        $this->addSql('DROP INDEX IDX_F7EFEA5E5C5A6AAC ON prix');
        $this->addSql('ALTER TABLE prix DROP info_du_prix_id, CHANGE prix_affiche prix_affiche VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE avis');
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY FK_B26681EF135FD3D');
        $this->addSql('ALTER TABLE info_du_prix DROP FOREIGN KEY FK_57576F3C4ADA8B82');
        $this->addSql('DROP INDEX IDX_57576F3C4ADA8B82 ON info_du_prix');
        $this->addSql('ALTER TABLE info_du_prix DROP id_prix_id');
        $this->addSql('ALTER TABLE prix ADD info_du_prix_id INT DEFAULT NULL, CHANGE prix_affiche prix_affiche DOUBLE PRECISION NOT NULL');
        $this->addSql('CREATE INDEX IDX_F7EFEA5E5C5A6AAC ON prix (info_du_prix_id)');
    }
}
