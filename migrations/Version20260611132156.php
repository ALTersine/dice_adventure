<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260611132156 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE frais_km (id INT AUTO_INCREMENT NOT NULL, info VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681EF135FD3D FOREIGN KEY (image_devenement_id) REFERENCES image_devenement (id)');
        $this->addSql('ALTER TABLE prix ADD CONSTRAINT FK_F7EFEA5E5C5A6AAC FOREIGN KEY (info_du_prix_id) REFERENCES info_du_prix (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE frais_km');
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY FK_B26681EF135FD3D');
        $this->addSql('ALTER TABLE prix DROP FOREIGN KEY FK_F7EFEA5E5C5A6AAC');
    }
}
