<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250116140322 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE composition_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE composition_non_sync_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE composition_partial_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE material_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE material_partial_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE product_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE product_deep_embeded_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE product_non_sync_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE product_partial_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE product_relations_non_sync_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE properties_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE properties_partial_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE company (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE composition (id INT NOT NULL, product_id INT DEFAULT NULL, material_id INT DEFAULT NULL, value INT NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C7F43474584665A ON composition (product_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C7F4347E308AC6F ON composition (material_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE composition_non_sync (id INT NOT NULL, product_id INT DEFAULT NULL, material_id INT DEFAULT NULL, value INT NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6ACC3A34584665A ON composition_non_sync (product_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6ACC3A3E308AC6F ON composition_non_sync (material_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE composition_partial (id INT NOT NULL, product_id INT DEFAULT NULL, material_id INT DEFAULT NULL, value INT NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_B770C78C4584665A ON composition_partial (product_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_B770C78CE308AC6F ON composition_partial (material_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE composition_static_id (id INT NOT NULL, product_id INT DEFAULT NULL, material_id INT DEFAULT NULL, value INT NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_FACB46EF4584665A ON composition_static_id (product_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_FACB46EFE308AC6F ON composition_static_id (material_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE material (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE material_non_sync (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE material_partial (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE material_static_id (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE product (id INT NOT NULL, properties_id INT DEFAULT NULL, custom_id UUID DEFAULT NULL, colors JSON NOT NULL, photos JSON NOT NULL, pattern VARCHAR(255) DEFAULT NULL, price_price INT DEFAULT NULL, price_currency VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_D34A04AD3691D1CA ON product (properties_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN product.custom_id IS '(DC2Type:custom_id)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE product_deep_embeded (id INT NOT NULL, emb_price2_type VARCHAR(255) DEFAULT NULL, emb_price2_emb_price_name VARCHAR(255) DEFAULT NULL, emb_price2_emb_price_price_value_price INT DEFAULT NULL, emb_price2_emb_price_price_value_currency VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE product_non_sync (id INT NOT NULL, pattern VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE product_partial (id INT NOT NULL, properties_id INT DEFAULT NULL, colors JSON NOT NULL, pattern VARCHAR(255) DEFAULT NULL, price_price INT DEFAULT NULL, price_currency VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_7BDECB443691D1CA ON product_partial (properties_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE product_relations_non_sync (id INT NOT NULL, properties_id INT DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_14854E7C3691D1CA ON product_relations_non_sync (properties_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE product_static_id (id INT NOT NULL, colors JSON NOT NULL, pattern VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE properties (id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, value VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE properties_partial (id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, value VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE properties_static_id (id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, value VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE composition ADD CONSTRAINT FK_C7F43474584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE composition ADD CONSTRAINT FK_C7F4347E308AC6F FOREIGN KEY (material_id) REFERENCES material (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE composition_non_sync ADD CONSTRAINT FK_6ACC3A34584665A FOREIGN KEY (product_id) REFERENCES product_relations_non_sync (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE composition_non_sync ADD CONSTRAINT FK_6ACC3A3E308AC6F FOREIGN KEY (material_id) REFERENCES material_non_sync (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE composition_partial ADD CONSTRAINT FK_B770C78C4584665A FOREIGN KEY (product_id) REFERENCES product_partial (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE composition_partial ADD CONSTRAINT FK_B770C78CE308AC6F FOREIGN KEY (material_id) REFERENCES material_partial (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE composition_static_id ADD CONSTRAINT FK_FACB46EF4584665A FOREIGN KEY (product_id) REFERENCES product_static_id (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE composition_static_id ADD CONSTRAINT FK_FACB46EFE308AC6F FOREIGN KEY (material_id) REFERENCES material_static_id (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product ADD CONSTRAINT FK_D34A04AD3691D1CA FOREIGN KEY (properties_id) REFERENCES properties (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_partial ADD CONSTRAINT FK_7BDECB443691D1CA FOREIGN KEY (properties_id) REFERENCES properties_partial (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_relations_non_sync ADD CONSTRAINT FK_14854E7C3691D1CA FOREIGN KEY (properties_id) REFERENCES properties (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE composition_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE composition_non_sync_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE composition_partial_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE material_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE material_partial_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE product_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE product_deep_embeded_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE product_non_sync_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE product_partial_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE product_relations_non_sync_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE properties_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE properties_partial_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE composition DROP CONSTRAINT FK_C7F43474584665A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE composition DROP CONSTRAINT FK_C7F4347E308AC6F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE composition_non_sync DROP CONSTRAINT FK_6ACC3A34584665A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE composition_non_sync DROP CONSTRAINT FK_6ACC3A3E308AC6F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE composition_partial DROP CONSTRAINT FK_B770C78C4584665A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE composition_partial DROP CONSTRAINT FK_B770C78CE308AC6F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE composition_static_id DROP CONSTRAINT FK_FACB46EF4584665A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE composition_static_id DROP CONSTRAINT FK_FACB46EFE308AC6F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product DROP CONSTRAINT FK_D34A04AD3691D1CA
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_partial DROP CONSTRAINT FK_7BDECB443691D1CA
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_relations_non_sync DROP CONSTRAINT FK_14854E7C3691D1CA
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE company
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE composition
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE composition_non_sync
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE composition_partial
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE composition_static_id
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE material
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE material_non_sync
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE material_partial
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE material_static_id
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE product
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE product_deep_embeded
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE product_non_sync
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE product_partial
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE product_relations_non_sync
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE product_static_id
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE properties
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE properties_partial
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE properties_static_id
        SQL);
    }
}
