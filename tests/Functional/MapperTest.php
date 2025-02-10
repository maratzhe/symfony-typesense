<?php

declare(strict_types=1);

namespace Functional;


use App\Entity\Composition;
use App\Entity\Product;
use App\Entity\ProductDeepEmbeded;
use App\Entity\ProductPartial;
use App\Entity\Properties;
use App\Value\Price;
use Doctrine\ORM\EntityManagerInterface;
use Maratzhe\SymfonyTypesense\Search\ClassMeta;
use Maratzhe\SymfonyTypesense\Service\Mapper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;



class MapperTest extends KernelTestCase
{
    public function testMapEmbeded() : void
    {
        $mapping        = Mapper::mapClass(Product::class, $this->em());

        self::assertInstanceOf(ClassMeta::class, $mapping);
        self::assertEquals(Product::class, $mapping->class);
        self::assertCount(7, $mapping->fields);
        self::assertCount(2, $mapping->relations);
        self::assertArrayHasKey('price', $mapping->fields);
        self::assertEquals(Price::class,  $mapping->fields['price']->embeddedClass);
    }

    public function testCollectionDeepEmbeded() : void
    {
        $mapping        = Mapper::mapClasses($this->em());
        $collection     = Mapper::mapCollection(ProductDeepEmbeded::class, $mapping);
        self::assertNotNull($collection);

        self::assertCount(5, $collection['fields']);
    }

    public function testCollectionPartial() : void
    {
        $mapping        = Mapper::mapClasses($this->em());
        $collection     = Mapper::mapCollection(ProductPartial::class, $mapping);
        self::assertNotNull($collection);

        self::assertCount(10, $collection['fields']);
    }

    public function testMapRelations() : void
    {
        $mapping    = Mapper::mapClass(Properties::class, $this->em());
        self::assertInstanceOf(ClassMeta::class, $mapping);
        self::assertCount(3, $mapping->fields);
        self::assertCount(0, $mapping->relations);


        $mapping    = Mapper::mapClass(Composition::class, $this->em());
        self::assertInstanceOf(ClassMeta::class, $mapping);
        self::assertCount(2, $mapping->fields);
        self::assertCount(2, $mapping->relations);
    }

    public function testCollections() : void
    {
        $mapping        = Mapper::mapClasses($this->em());
        $collections    = Mapper::mapCollections($mapping);

        self::assertCount(7, $collections);
    }

    public function testMapping() : void
    {
        $expected   = [
            0 => [
                'name'      => 'id',
                'type'      => 'int32',
                'locale'    => '',
                'optional'  => true,
                'facet'     => false,
                'index'     => false,
                'infix'     => false,
                'sort'      => false,
                'stem'      => false
            ],
            1 => [
                'name'      => 'custom_id',
                'type'      => 'string',
                'locale'    => '',
                'optional'  => true,
                'facet'     => false,
                'index'     => false,
                'infix'     => false,
                'sort'      => false,
                'stem'      => false
            ],
            2 => [
                'name'      => 'colors',
                'type'      => 'int32[]',
                'locale'    => '',
                'optional'  => true,
                'facet'     => true,
                'index'     => true,
                'infix'     => false,
                'sort'      => false,
                'stem'      => false
            ],
            3 => [
                'name'      => 'photos.*',
                'type'      => 'object[]',
                'locale'    => '',
                'optional'  => true,
                'facet'     => false,
                'index'     => false,
                'infix'     => false,
                'sort'      => false,
                'stem'      => false
            ],
            4 => [
                'name'      => 'photos.*.size',
                'type'      => 'int32',
                'locale'    => '',
                'optional'  => true,
                'facet'     => false,
                'index'     => false,
                'infix'     => false,
                'sort'      => false,
                'stem'      => false
            ],
            5 => [
                'name'      => 'photos.*.url',
                'type'      => 'string',
                'locale'    => '',
                'optional'  => true,
                'facet'     => false,
                'index'     => false,
                'infix'     => false,
                'sort'      => false,
                'stem'      => false
            ],
            6 => [
                'name'      => 'pattern',
                'type'      => 'string',
                'locale'    => '',
                'optional'  => true,
                'facet'     => true,
                'index'     => true,
                'infix'     => false,
                'sort'      => false,
                'stem'      => false
            ],
            7 => [
                'name'      => 'price.price',
                'type'      => 'int32',
                'locale'    => '',
                'optional'  => true,
                'facet'     => false,
                'index'     => false,
                'infix'     => false,
                'sort'      => false,
                'stem'      => false
            ],
            8 => [
                'name'      => 'price.currency',
                'type'      => 'string',
                'locale'    => '',
                'optional'  => true,
                'facet'     => false,
                'index'     => false,
                'infix'     => false,
                'sort'      => false,
                'stem'      => false
            ],
            9 => [
                'name'      => 'published',
                'type'      => 'bool',
                'locale'    => '',
                'optional'  => true,
                'facet'     => false,
                'index'     => true,
                'infix'     => false,
                'sort'      => false,
                'stem'      => false
            ],
            10 => [
                'name'      => 'compositions.*',
                'type'      => 'object[]',
                'locale'    => '',
                'optional'  => true,
                'facet'     => false,
                'index'     => false,
                'infix'     => false,
                'sort'      => false,
                'stem'      => false
            ],
            11 => [
                'name'      => 'compositions.*.id',
                'type'      => 'int32',
                'locale'    => '',
                'optional'  => true,
                'facet'     => false,
                'index'     => false,
                'infix'     => false,
                'sort'      => false,
                'stem'      => false
            ],
            12 => [
                'name'      => 'compositions.*.value',
                'type'      => 'int32',
                'locale'    => '',
                'optional'  => true,
                'facet'     => false,
                'index'     => false,
                'infix'     => false,
                'sort'      => false,
                'stem'      => false
            ],
            13 => [
                'name'      => 'compositions.*.material',
                'type'      => 'object',
                'locale'    => '',
                'optional'  => true,
                'facet'     => false,
                'index'     => false,
                'infix'     => false,
                'sort'      => false,
                'stem'      => false
            ],
            14 => [
                'name'      => 'compositions.*.material.id',
                'type'      => 'int32',
                'locale'    => '',
                'optional'  => true,
                'facet'     => false,
                'index'     => false,
                'infix'     => false,
                'sort'      => false,
                'stem'      => false
            ],
            15 => [
                'name'      => 'compositions.*.material.name',
                'type'      => 'string',
                'locale'    => '',
                'optional'  => true,
                'facet'     => false,
                'index'     => false,
                'infix'     => false,
                'sort'      => false,
                'stem'      => false
            ],
            16 => [
                'name'      => 'properties',
                'type'      => 'object',
                'locale'    => '',
                'optional'  => true,
                'facet'     => false,
                'index'     => false,
                'infix'     => false,
                'sort'      => false,
                'stem'      => false
            ],
            17 => [
                'name'      => 'properties.id',
                'type'      => 'int32',
                'locale'    => '',
                'optional'  => true,
                'facet'     => false,
                'index'     => false,
                'infix'     => false,
                'sort'      => false,
                'stem'      => false
            ],
            18 => [
                'name'      => 'properties.name',
                'type'      => 'string',
                'locale'    => '',
                'optional'  => true,
                'facet'     => false,
                'index'     => false,
                'infix'     => false,
                'sort'      => false,
                'stem'      => false
            ],
            19 => [
                'name'      => 'properties.value',
                'type'      => 'string',
                'locale'    => '',
                'optional'  => true,
                'facet'     => false,
                'index'     => false,
                'infix'     => false,
                'sort'      => false,
                'stem'      => false
            ]
        ];



        $mapper     = $this->mapper();
        $mapping    = $mapper->mapping(Product::class);

        self::assertMappingEquals($expected, $mapping['fields']);
    }

    public function testDeepEmbeded() : void
    {
        $expected   = [
            0 => [
                'name'      => 'id',
                'type'      => 'int32',
                'locale'    => '',
                'optional'  => true,
                'facet'     => false,
                'index'     => false,
                'infix'     => false,
                'sort'      => false,
                'stem'      => false
            ],
            1 => [
                'name'      => 'emb_price2.type',
                'type'      => 'string',
                'locale'    => '',
                'optional'  => true,
                'facet'     => false,
                'index'     => false,
                'infix'     => false,
                'sort'      => false,
                'stem'      => false
            ],
            2 => [
                'name'      => 'emb_price2.emb_price.name',
                'type'      => 'string',
                'locale'    => '',
                'optional'  => true,
                'facet'     => false,
                'index'     => false,
                'infix'     => false,
                'sort'      => false,
                'stem'      => false
            ],
            3 => [
                'name'      => 'emb_price2.emb_price.price_value.price',
                'type'      => 'int32',
                'locale'    => '',
                'optional'  => true,
                'facet'     => false,
                'index'     => false,
                'infix'     => false,
                'sort'      => false,
                'stem'      => false
            ],
            4 => [
                'name'      => 'emb_price2.emb_price.price_value.currency',
                'type'      => 'string',
                'locale'    => '',
                'optional'  => true,
                'facet'     => false,
                'index'     => false,
                'infix'     => false,
                'sort'      => false,
                'stem'      => false
            ],
        ];

        $mapper     = $this->mapper();
        $mapping    = $mapper->mapping(ProductDeepEmbeded::class);

        self::assertMappingEquals($expected, $mapping['fields']);
    }

    public function testPartial() : void
    {
        $expected   = [
            0 => [
                'name'      => 'id',
                'type'      => 'int32',
                'locale'    => '',
                'optional'  => true,
                'facet'     => false,
                'index'     => false,
                'infix'     => false,
                'sort'      => false,
                'stem'      => false
            ],
            1 => [
                'name'      => 'price.price',
                'type'      => 'int32',
                'locale'    => '',
                'optional'  => true,
                'facet'     => false,
                'index'     => false,
                'infix'     => false,
                'sort'      => false,
                'stem'      => false
            ],
            2 => [
                'name'      => 'compositions.*',
                'type'      => 'object[]',
                'locale'    => '',
                'optional'  => true,
                'facet'     => false,
                'index'     => false,
                'infix'     => false,
                'sort'      => false,
                'stem'      => false
            ],
            3 => [
                'name'      => 'compositions.*.id',
                'type'      => 'int32',
                'locale'    => '',
                'optional'  => true,
                'facet'     => false,
                'index'     => false,
                'infix'     => false,
                'sort'      => false,
                'stem'      => false
            ],
            4 => [
                'name'      => 'compositions.*.material',
                'type'      => 'object',
                'locale'    => '',
                'optional'  => true,
                'facet'     => false,
                'index'     => false,
                'infix'     => false,
                'sort'      => false,
                'stem'      => false
            ],
            5 => [
                'name'      => 'compositions.*.material.id',
                'type'      => 'int32',
                'locale'    => '',
                'optional'  => true,
                'facet'     => false,
                'index'     => false,
                'infix'     => false,
                'sort'      => false,
                'stem'      => false
            ],
            6 => [
                'name'      => 'compositions.*.material.name',
                'type'      => 'string',
                'locale'    => '',
                'optional'  => true,
                'facet'     => false,
                'index'     => false,
                'infix'     => false,
                'sort'      => false,
                'stem'      => false
            ],
            7 => [
                'name'      => 'properties',
                'type'      => 'object',
                'locale'    => '',
                'optional'  => true,
                'facet'     => false,
                'index'     => false,
                'infix'     => false,
                'sort'      => false,
                'stem'      => false
            ],
            8 => [
                'name'      => 'properties.id',
                'type'      => 'int32',
                'locale'    => '',
                'optional'  => true,
                'facet'     => false,
                'index'     => false,
                'infix'     => false,
                'sort'      => false,
                'stem'      => false
            ],
            9 => [
                'name'      => 'properties.name',
                'type'      => 'string',
                'locale'    => '',
                'optional'  => true,
                'facet'     => false,
                'index'     => false,
                'infix'     => false,
                'sort'      => false,
                'stem'      => false
            ],
        ];

        $mapper     = $this->mapper();
        $mapping    = $mapper->mapping(ProductPartial::class);

        self::assertMappingEquals($expected, $mapping['fields']);
    }

    /**
     * @param array<int, array<string, bool|int|float|string|null>> $expected
     * @param array<int, array<string, bool|int|float|string|null>> $actual
     * @return void
     */
    protected function assertMappingEquals(array $expected, array $actual) : void
    {
        self::assertCount(count($expected), $actual);

        for ($i=0; $i<count($actual); $i++) {
            self::assertEquals($expected[$i], $actual[$i], 'index: ' . $i);
        }
    }

    protected function mapper() : Mapper
    {
        /** @var Mapper $mapper */
        $mapper = self::getContainer()->get(Mapper::class);

        return $mapper;
    }

    protected function em() : EntityManagerInterface
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        return $em;
    }
}