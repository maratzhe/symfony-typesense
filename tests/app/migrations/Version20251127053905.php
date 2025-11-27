<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251127053905 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE company_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE composition_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE composition_non_sync_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE composition_partial_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE material_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE material_partial_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE product_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE product_deep_embeded_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE product_many_to_one_relation_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE product_non_sync_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE product_partial_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE product_relations_non_sync_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE properties_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE properties_partial_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE company (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE composition (id INT NOT NULL, product_id INT DEFAULT NULL, material_id INT DEFAULT NULL, value INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C7F43474584665A ON composition (product_id)');
        $this->addSql('CREATE INDEX IDX_C7F4347E308AC6F ON composition (material_id)');
        $this->addSql('CREATE TABLE composition_non_sync (id INT NOT NULL, product_id INT DEFAULT NULL, material_id INT DEFAULT NULL, value INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6ACC3A34584665A ON composition_non_sync (product_id)');
        $this->addSql('CREATE INDEX IDX_6ACC3A3E308AC6F ON composition_non_sync (material_id)');
        $this->addSql('CREATE TABLE composition_partial (id INT NOT NULL, product_id INT DEFAULT NULL, material_id INT DEFAULT NULL, value INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B770C78C4584665A ON composition_partial (product_id)');
        $this->addSql('CREATE INDEX IDX_B770C78CE308AC6F ON composition_partial (material_id)');
        $this->addSql('CREATE TABLE composition_static_id (id INT NOT NULL, product_id INT DEFAULT NULL, material_id INT DEFAULT NULL, value INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FACB46EF4584665A ON composition_static_id (product_id)');
        $this->addSql('CREATE INDEX IDX_FACB46EFE308AC6F ON composition_static_id (material_id)');
        $this->addSql('CREATE TABLE material (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE material_non_sync (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE material_partial (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE material_static_id (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE product (id INT NOT NULL, properties_id INT DEFAULT NULL, custom_id UUID DEFAULT NULL, colors JSON NOT NULL, photos JSON NOT NULL, pattern VARCHAR(255) DEFAULT NULL, published BOOLEAN NOT NULL, price_price INT DEFAULT NULL, price_currency VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D34A04AD3691D1CA ON product (properties_id)');
        $this->addSql('COMMENT ON COLUMN product.custom_id IS \'(DC2Type:custom_id)\'');
        $this->addSql('CREATE TABLE product_deep_embeded (id INT NOT NULL, emb_price2_type VARCHAR(255) DEFAULT NULL, emb_price2_emb_price_name VARCHAR(255) DEFAULT NULL, emb_price2_emb_price_price_value_price INT DEFAULT NULL, emb_price2_emb_price_price_value_currency VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE product_many_to_one_relation (id INT NOT NULL, company_id INT DEFAULT NULL, pattern VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_34980A53979B1AD6 ON product_many_to_one_relation (company_id)');
        $this->addSql('CREATE TABLE product_non_sync (id INT NOT NULL, pattern VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE product_partial (id INT NOT NULL, properties_id INT DEFAULT NULL, colors JSON NOT NULL, pattern VARCHAR(255) DEFAULT NULL, price_price INT DEFAULT NULL, price_currency VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7BDECB443691D1CA ON product_partial (properties_id)');
        $this->addSql('CREATE TABLE product_relations_non_sync (id INT NOT NULL, properties_id INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_14854E7C3691D1CA ON product_relations_non_sync (properties_id)');
        $this->addSql('CREATE TABLE product_static_id (id INT NOT NULL, colors JSON NOT NULL, pattern VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE properties (id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, value VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE properties_partial (id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, value VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE properties_static_id (id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, value VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE composition ADD CONSTRAINT FK_C7F43474584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE composition ADD CONSTRAINT FK_C7F4347E308AC6F FOREIGN KEY (material_id) REFERENCES material (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE composition_non_sync ADD CONSTRAINT FK_6ACC3A34584665A FOREIGN KEY (product_id) REFERENCES product_relations_non_sync (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE composition_non_sync ADD CONSTRAINT FK_6ACC3A3E308AC6F FOREIGN KEY (material_id) REFERENCES material_non_sync (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE composition_partial ADD CONSTRAINT FK_B770C78C4584665A FOREIGN KEY (product_id) REFERENCES product_partial (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE composition_partial ADD CONSTRAINT FK_B770C78CE308AC6F FOREIGN KEY (material_id) REFERENCES material_partial (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE composition_static_id ADD CONSTRAINT FK_FACB46EF4584665A FOREIGN KEY (product_id) REFERENCES product_static_id (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE composition_static_id ADD CONSTRAINT FK_FACB46EFE308AC6F FOREIGN KEY (material_id) REFERENCES material_static_id (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD3691D1CA FOREIGN KEY (properties_id) REFERENCES properties (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_many_to_one_relation ADD CONSTRAINT FK_34980A53979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_partial ADD CONSTRAINT FK_7BDECB443691D1CA FOREIGN KEY (properties_id) REFERENCES properties_partial (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_relations_non_sync ADD CONSTRAINT FK_14854E7C3691D1CA FOREIGN KEY (properties_id) REFERENCES properties (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE company_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE composition_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE composition_non_sync_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE composition_partial_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE material_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE material_partial_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE product_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE product_deep_embeded_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE product_many_to_one_relation_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE product_non_sync_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE product_partial_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE product_relations_non_sync_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE properties_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE properties_partial_id_seq CASCADE');
        $this->addSql('ALTER TABLE composition DROP CONSTRAINT FK_C7F43474584665A');
        $this->addSql('ALTER TABLE composition DROP CONSTRAINT FK_C7F4347E308AC6F');
        $this->addSql('ALTER TABLE composition_non_sync DROP CONSTRAINT FK_6ACC3A34584665A');
        $this->addSql('ALTER TABLE composition_non_sync DROP CONSTRAINT FK_6ACC3A3E308AC6F');
        $this->addSql('ALTER TABLE composition_partial DROP CONSTRAINT FK_B770C78C4584665A');
        $this->addSql('ALTER TABLE composition_partial DROP CONSTRAINT FK_B770C78CE308AC6F');
        $this->addSql('ALTER TABLE composition_static_id DROP CONSTRAINT FK_FACB46EF4584665A');
        $this->addSql('ALTER TABLE composition_static_id DROP CONSTRAINT FK_FACB46EFE308AC6F');
        $this->addSql('ALTER TABLE product DROP CONSTRAINT FK_D34A04AD3691D1CA');
        $this->addSql('ALTER TABLE product_many_to_one_relation DROP CONSTRAINT FK_34980A53979B1AD6');
        $this->addSql('ALTER TABLE product_partial DROP CONSTRAINT FK_7BDECB443691D1CA');
        $this->addSql('ALTER TABLE product_relations_non_sync DROP CONSTRAINT FK_14854E7C3691D1CA');
        $this->addSql('DROP TABLE company');
        $this->addSql('DROP TABLE composition');
        $this->addSql('DROP TABLE composition_non_sync');
        $this->addSql('DROP TABLE composition_partial');
        $this->addSql('DROP TABLE composition_static_id');
        $this->addSql('DROP TABLE material');
        $this->addSql('DROP TABLE material_non_sync');
        $this->addSql('DROP TABLE material_partial');
        $this->addSql('DROP TABLE material_static_id');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE product_deep_embeded');
        $this->addSql('DROP TABLE product_many_to_one_relation');
        $this->addSql('DROP TABLE product_non_sync');
        $this->addSql('DROP TABLE product_partial');
        $this->addSql('DROP TABLE product_relations_non_sync');
        $this->addSql('DROP TABLE product_static_id');
        $this->addSql('DROP TABLE properties');
        $this->addSql('DROP TABLE properties_partial');
        $this->addSql('DROP TABLE properties_static_id');
    }
}
