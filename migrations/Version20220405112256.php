<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220405112256 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create task table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE task 
            (
                uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', 
                title VARCHAR(255) NOT NULL, 
                description LONGTEXT DEFAULT NULL, 
                deadline DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', 
                completed TINYINT(1) DEFAULT 0 NOT NULL, 
                PRIMARY KEY(uuid)
            ) 
            DEFAULT CHARACTER SET utf8mb4 
            COLLATE `utf8mb4_unicode_ci` 
            ENGINE = InnoDB'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE task');
    }
}
