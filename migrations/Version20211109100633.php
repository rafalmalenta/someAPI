<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211109100633 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE author (email VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, name VARCHAR(50) NOT NULL, surname VARCHAR(100) NOT NULL, PRIMARY KEY(email)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE book (isbn VARCHAR(13) NOT NULL, author_email VARCHAR(180) DEFAULT NULL, title VARCHAR(200) NOT NULL, description LONGTEXT NOT NULL, created DATETIME NOT NULL, INDEX IDX_CBE5A331F11A5CB9 (author_email), PRIMARY KEY(isbn)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE opinion (id INT AUTO_INCREMENT NOT NULL, book_isbn VARCHAR(13) DEFAULT NULL, rating SMALLINT NOT NULL, description LONGTEXT NOT NULL, author VARCHAR(100) NOT NULL, created DATE NOT NULL, email VARCHAR(100) DEFAULT NULL, INDEX IDX_AB02B027D581BFEE (book_isbn), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE book ADD CONSTRAINT FK_CBE5A331F11A5CB9 FOREIGN KEY (author_email) REFERENCES author (email)');
        $this->addSql('ALTER TABLE opinion ADD CONSTRAINT FK_AB02B027D581BFEE FOREIGN KEY (book_isbn) REFERENCES book (isbn)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE book DROP FOREIGN KEY FK_CBE5A331F11A5CB9');
        $this->addSql('ALTER TABLE opinion DROP FOREIGN KEY FK_AB02B027D581BFEE');
        $this->addSql('DROP TABLE author');
        $this->addSql('DROP TABLE book');
        $this->addSql('DROP TABLE opinion');
    }
}
