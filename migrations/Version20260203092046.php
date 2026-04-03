<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260203092046 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE generation_user_contact (generation_id INT NOT NULL, user_contact_id INT NOT NULL, INDEX IDX_59D39840553A6EC4 (generation_id), INDEX IDX_59D3984040C6E3A6 (user_contact_id), PRIMARY KEY (generation_id, user_contact_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE generation_user_contact ADD CONSTRAINT FK_59D39840553A6EC4 FOREIGN KEY (generation_id) REFERENCES generation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE generation_user_contact ADD CONSTRAINT FK_59D3984040C6E3A6 FOREIGN KEY (user_contact_id) REFERENCES user_contact (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE generation DROP FOREIGN KEY `FK_D3266C3BA76ED395`');
        $this->addSql('DROP INDEX IDX_D3266C3BA76ED395 ON generation');
        $this->addSql('ALTER TABLE generation ADD file VARCHAR(255) NOT NULL, ADD created_at DATETIME NOT NULL, ADD user_id_id INT DEFAULT NULL, DROP user_id');
        $this->addSql('ALTER TABLE generation ADD CONSTRAINT FK_D3266C3B9D86650F FOREIGN KEY (user_id_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_D3266C3B9D86650F ON generation (user_id_id)');
        $this->addSql('ALTER TABLE plan CHANGE name name VARCHAR(255) NOT NULL, CHANGE description description LONGTEXT NOT NULL, CHANGE limit_generation limit_generation INT NOT NULL, CHANGE role role VARCHAR(255) DEFAULT NULL, CHANGE price price DOUBLE PRECISION NOT NULL, CHANGE special_price special_price DOUBLE PRECISION DEFAULT NULL, CHANGE special_price_from special_price_from DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE lastname lastname VARCHAR(255) NOT NULL, CHANGE firstname firstname VARCHAR(255) NOT NULL, CHANGE favorite_color favorite_color VARCHAR(255) DEFAULT NULL, CHANGE phone phone VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user_contact DROP FOREIGN KEY `FK_146FF832A76ED395`');
        $this->addSql('DROP INDEX IDX_146FF832A76ED395 ON user_contact');
        $this->addSql('ALTER TABLE user_contact ADD user_id_id INT DEFAULT NULL, DROP user_id, CHANGE lastname lastname VARCHAR(255) NOT NULL, CHANGE firstname firstname VARCHAR(255) NOT NULL, CHANGE email email VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE user_contact ADD CONSTRAINT FK_146FF8329D86650F FOREIGN KEY (user_id_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_146FF8329D86650F ON user_contact (user_id_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE generation_user_contact DROP FOREIGN KEY FK_59D39840553A6EC4');
        $this->addSql('ALTER TABLE generation_user_contact DROP FOREIGN KEY FK_59D3984040C6E3A6');
        $this->addSql('DROP TABLE generation_user_contact');
        $this->addSql('ALTER TABLE generation DROP FOREIGN KEY FK_D3266C3B9D86650F');
        $this->addSql('DROP INDEX IDX_D3266C3B9D86650F ON generation');
        $this->addSql('ALTER TABLE generation ADD user_id INT NOT NULL, DROP file, DROP created_at, DROP user_id_id');
        $this->addSql('ALTER TABLE generation ADD CONSTRAINT `FK_D3266C3BA76ED395` FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_D3266C3BA76ED395 ON generation (user_id)');
        $this->addSql('ALTER TABLE plan CHANGE name name VARCHAR(100) NOT NULL, CHANGE description description LONGTEXT DEFAULT NULL, CHANGE limit_generation limit_generation INT DEFAULT NULL, CHANGE role role VARCHAR(50) DEFAULT NULL, CHANGE price price NUMERIC(10, 2) NOT NULL, CHANGE special_price special_price NUMERIC(10, 2) DEFAULT NULL, CHANGE special_price_from special_price_from NUMERIC(10, 0) NOT NULL');
        $this->addSql('ALTER TABLE `user` CHANGE lastname lastname VARCHAR(100) NOT NULL, CHANGE firstname firstname VARCHAR(100) NOT NULL, CHANGE favorite_color favorite_color VARCHAR(7) DEFAULT NULL, CHANGE phone phone VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE user_contact DROP FOREIGN KEY FK_146FF8329D86650F');
        $this->addSql('DROP INDEX IDX_146FF8329D86650F ON user_contact');
        $this->addSql('ALTER TABLE user_contact ADD user_id INT NOT NULL, DROP user_id_id, CHANGE lastname lastname VARCHAR(100) NOT NULL, CHANGE firstname firstname VARCHAR(100) NOT NULL, CHANGE email email VARCHAR(180) NOT NULL');
        $this->addSql('ALTER TABLE user_contact ADD CONSTRAINT `FK_146FF832A76ED395` FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_146FF832A76ED395 ON user_contact (user_id)');
    }
}
