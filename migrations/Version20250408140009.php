<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250408140009 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE commande (id SERIAL NOT NULL, event_id INT NOT NULL, date_commande TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, total DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6EEAA67D71F7E88B ON commande (event_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE event (id SERIAL NOT NULL, nom VARCHAR(255) NOT NULL, dates JSON NOT NULL, lieu VARCHAR(255) NOT NULL, capacite INT NOT NULL, prix_par_jour JSON NOT NULL, prix_multipass DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE ticket (id SERIAL NOT NULL, order_id INT NOT NULL, type VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, date DATE DEFAULT NULL, prix DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_97A0ADA38D9F6D38 ON ticket (order_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA38D9F6D38 FOREIGN KEY (order_id) REFERENCES commande (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande DROP CONSTRAINT FK_6EEAA67D71F7E88B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ticket DROP CONSTRAINT FK_97A0ADA38D9F6D38
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE commande
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE event
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE ticket
        SQL);
    }
}
