<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190320230326 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE card (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, type INT NOT NULL, strengh INT NOT NULL, team INT NOT NULL, picture VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE game (id INT AUTO_INCREMENT NOT NULL, player_one_id INT NOT NULL, player_two_id INT NOT NULL, winner_id INT DEFAULT NULL, created_at DATETIME NOT NULL, ended_at DATETIME NOT NULL, status INT NOT NULL, field LONGTEXT NOT NULL, INDEX IDX_232B318C649A58CD (player_one_id), INDEX IDX_232B318CFC6BF02 (player_two_id), INDEX IDX_232B318C5DFCD4B8 (winner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, status INT NOT NULL, username VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, rank INT NOT NULL, picture VARCHAR(255) NOT NULL, roles JSON NOT NULL, reset_password VARCHAR(255) DEFAULT NULL, reset_token VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318C649A58CD FOREIGN KEY (player_one_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318CFC6BF02 FOREIGN KEY (player_two_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318C5DFCD4B8 FOREIGN KEY (winner_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318C649A58CD');
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318CFC6BF02');
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318C5DFCD4B8');
        $this->addSql('DROP TABLE card');
        $this->addSql('DROP TABLE game');
        $this->addSql('DROP TABLE user');
    }
}
