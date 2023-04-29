<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230421093842 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bag (id INT AUTO_INCREMENT NOT NULL, use_id_id INT NOT NULL, purchase_date DATETIME NOT NULL, status TINYINT(1) NOT NULL, INDEX IDX_1B226841E3D2B46D (use_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bag_item (id INT AUTO_INCREMENT NOT NULL, product_id_id INT NOT NULL, bag_id_id INT NOT NULL, quantity INT NOT NULL, date DATETIME NOT NULL, INDEX IDX_4BC0A204DE18E50B (product_id_id), INDEX IDX_4BC0A2048272BCAA (bag_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE bag ADD CONSTRAINT FK_1B226841E3D2B46D FOREIGN KEY (use_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE bag_item ADD CONSTRAINT FK_4BC0A204DE18E50B FOREIGN KEY (product_id_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE bag_item ADD CONSTRAINT FK_4BC0A2048272BCAA FOREIGN KEY (bag_id_id) REFERENCES bag (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bag DROP FOREIGN KEY FK_1B226841E3D2B46D');
        $this->addSql('ALTER TABLE bag_item DROP FOREIGN KEY FK_4BC0A204DE18E50B');
        $this->addSql('ALTER TABLE bag_item DROP FOREIGN KEY FK_4BC0A2048272BCAA');
        $this->addSql('DROP TABLE bag');
        $this->addSql('DROP TABLE bag_item');
    }
}
