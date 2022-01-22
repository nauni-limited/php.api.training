<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220122194703 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add task table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE task (
                id INT AUTO_INCREMENT NOT NULL, 
                title VARCHAR(255) NOT NULL, 
                description LONGTEXT DEFAULT NULL,
                deadline DATETIME DEFAULT NULL, 
                completed TINYINT(1) DEFAULT 0 NOT NULL, 
                PRIMARY KEY(id)
            ) 
            DEFAULT CHARACTER SET utf8mb4 
            COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE task');
    }
}
