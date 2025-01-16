<?php

declare(strict_types=1);

namespace Functional;

use App\Entity\Composition;
use App\Entity\CompositionPartial;
use App\Entity\Material;
use App\Entity\MaterialPartial;
use App\Entity\Product;
use App\Entity\ProductNonSync;
use App\Entity\ProductPartial;
use App\Entity\ProductRelationsNonSync;
use App\Entity\Properties;
use App\Entity\PropertiesPartial;
use App\Value\Color;
use App\Value\CustomId;
use App\Value\Pattern;
use App\Value\Photo;
use App\Value\Price;
use App\Value\PricePartial;
use Doctrine\ORM\EntityManagerInterface;
use Maratzhe\SymfonyTypesense\Factory\FinderFactory;
use Maratzhe\SymfonyTypesense\Service\CollectionManager;
use Maratzhe\SymfonyTypesense\Service\EventListener;
use Maratzhe\SymfonyTypesense\Service\Finder;
use Maratzhe\SymfonyTypesense\Service\Indexer;
use Maratzhe\SymfonyTypesense\Service\Transformer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;


class IndexerTest extends KernelTestCase
{
    use ResetDatabase;
    use Factories;

    public function testCreate() : void
    {
        $em = $this->em();

        $this->checkIndex();


        $material1 = new Material( 'denim');
        $material2 = new Material('cotton');

        $product    = new Product(
            new CustomId('01944071-3781-70e3-89aa-f00b80fd401d'),
            [Color::White, Color::Red],
            [new Photo(100, 'test_url')],
            Pattern::Animal,
            new Price(30, 'eur'),
            [new Composition( $material1, 30), new Composition( $material2, 70)],
            new Properties('test_name', 'test_value')
        );

        $product2    = new Product(
            new CustomId('01944071-3781-70e3-89aa-f00b80fd401d'),
            [Color::Black],
            [],
            Pattern::Animal,
            new Price(30, 'eur'),
            [new Composition( $material1, 30), new Composition( $material2, 70)],
            new Properties( 'test_name', 'test_value')
        );


        $em->persist($product);
        $em->persist($product2);


        $this->indexer()->persist($product);
        $this->indexer()->persist($product2);


        $data   = $this->indexer()->toPersist();


        $this->assertNotEmpty($data);
        $this->assertArrayHasKey(Product::class, $data);
        $this->assertCount(2, $data[Product::class]);
        $this->assertArrayHasKey($product->id, $data[Product::class]);
        $this->assertArrayHasKey($product2->id, $data[Product::class]);
        $this->assertCount(18, $data[Product::class][$product->id]);
        $this->assertCount(18, $data[Product::class][$product2->id]);



        $this->indexer()->flush();
        $this->assertEmpty($this->indexer()->toPersist());


        $searched   = $this->searchById($product->id, Product::class);
        $searched2  = $this->searchById($product2->id, Product::class);

        $this->assertNotNull($searched);
        $this->assertNotNull($searched2);

        $this->assertProductsEquals($product, $searched);
        $this->assertProductsEquals($product2, $searched2);
    }

    public function testCreatePartial() : void
    {
        $this->checkIndex();
        $em = $this->em();


        $product = new ProductPartial(
            [Color::White, Color::Red],
            Pattern::Camouflage,
            new PricePartial(30, 'eur'),
            [
                new CompositionPartial( new MaterialPartial('denim'), 30),
                new CompositionPartial(  new MaterialPartial( 'silk'), 70),
            ],
            new PropertiesPartial('test', 'value')
        );

        $em->persist($product);

        $this->indexer()->persist($product);

        $data   = $this->indexer()->toPersist();

        $this->assertArrayHasKey(ProductPartial::class, $data);
        $this->assertCount(1, $data[ProductPartial::class]);
        $this->assertArrayHasKey($product->id, $data[ProductPartial::class]);
        $this->assertCount(6, $data[ProductPartial::class][$product->id]);
    }

    public function testRemove() : void
    {
        $this->checkIndex();

        $product    = $this->getTestProduct();
        $productSr  = $this->searchById($product->id, $product::class);

        $this->assertNotNull($productSr);

        $this->indexer()->remove($product);

        $data   = $this->indexer()->toRemove();
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey(Product::class, $data);
        $this->assertCount(1, $data[Product::class]);
        $this->assertArrayHasKey(0, $data[Product::class]);
        $this->assertEquals($product->id, $data[Product::class][0]);

        $this->indexer()->flush();
        $this->assertEmpty($this->indexer()->toRemove());

        $productSr  = $this->searchById($product->id, $product::class);

        $this->assertNull($productSr);
    }

    public function testEdit() : void
    {
        $em         = $this->em();
        $product    = $this->getTestProduct();

        $this->assertCount(2, $product->colors);
        $this->assertEquals(Color::Red, $product->colors[1]);

        $product->colors    = [Color::White];

        $em->persist($product);
        $this->indexer()->persist($product);

        $data   = $this->indexer()->toPersist();

        $this->assertNotEmpty($data);
        $this->assertArrayHasKey(Product::class, $data);
        $this->assertCount(1, $data[Product::class]);

        $this->indexer()->flush();

        $data   = $this->indexer()->toPersist();
        $this->assertEmpty($data);

        $productSr  = $this->searchById($product->id, $product::class);
        $this->assertNotNull($productSr);

        $this->assertProductsEquals($product, $productSr);
    }

//    public function testEditRelation() : void
//    {
//        $em         = $this->em();
//        $product    = $this->getTestProduct(false);
//
//        $this->assertCount(2, $product->compositions);
//        $this->assertNotNull($product->compositions[1]);
//        $material   = $em->find(Material::class, $product->compositions[1]->material->id);
//
//        $this->assertNotNull($material);
//        $this->assertEquals('cotton', $material->name);
//
//        $material->name = 'silk';
//        $this->em()->persist($material);
//        $this->assertNotEmpty($material->compositions[0]);
//        dd( $material->compositions[0]->product);
//
//        $this->assertNotNull($material->compositions[0]->product);
//
//        $this->indexer()->persist($material->compositions[0]->product);
//
//        $data   = $this->indexer()->toPersist();
//        $this->assertCount(1, $data);
//
//        dd($data);
//
////        dd($data);
//
//        $this->em()->flush();
//
//
//        $product  = $this->find($product->id, $product::class);
//        $this->assertNotNull($product);
//        $productSr  = $this->searchById($product->id, $product::class);
//        $this->assertNotNull($productSr);
//
//        $this->assertNotNull($product->compositions[1]);
//        $material = $product->compositions[1]->material;
//        $this->assertEquals('silk', $material->name);
//
//        $this->assertProductsEquals($product, $productSr);
//    }
//
//    public function testAddRelation() : void
//    {
//        $product    = $this->getTestProduct();
//        $this->assertCount(2, $product->compositions);
//
//        $material               = new Material(3, 'test');
//        $composition            = new Composition(3, $material, 30);
//        $composition->product   = $product;
//        $material->compositions->add($composition);
//        $product->compositions->add($composition);
//
//        $this->em()->persist($material);
//        $this->em()->persist($composition);
//        $this->em()->flush();
//        $this->em()->clear();
//
//        $product    = $this->find($product->id, $product::class);
//        $this->assertNotNull($product);
//
//        $productSr  = $this->searchById($product->id, $product::class);
//        $this->assertNotNull($productSr);
//
//        $this->assertCount(3, $product->compositions);
//        $this->assertProductsEquals($product, $productSr);
//    }
//
//    public function testRemoveRelation() : void
//    {
//        $product    = $this->getTestProduct();
//
//        $product->compositions->remove(0);
//
//        $this->em()->flush();
//        $this->em()->clear();
//
//        $product    = $this->find($product->id, $product::class);
//        $this->assertNotNull($product);
//
//        $productSr  = $this->searchById($product->id, $product::class);
//        $this->assertNotNull($productSr);
//
//        $this->assertCount(1, $product->compositions);
//
//
//        $this->assertProductsEquals($product, $productSr);
//    }
//
//    public function testSyncNoneCollection() : void
//    {
//        $this->checkIndex();
//
//        $product    = new ProductNonSync(Pattern::Floral);
//        $this->em()->persist($product);
//        $this->em()->flush();
//
//        $productSr = $this->searchById($product->id, $product::class);
//        $this->assertNull($productSr);
//
//        $this->manager()->get(ProductNonSync::class)
//            ->documents
//            ->upsert($this->transformer()->normalize($product));
//
//        $productSr = $this->searchById($product->id, $product::class);
//
//        $this->assertNotNull($productSr);
//    }
//
//    public function testNonSyncRelation() : void
//    {
//        $this->checkIndex();
//
//        $material1 = new MaterialNonSync( 1,'denim');
//        $material2 = new MaterialNonSync(2,'cotton');
//
//        $product    = new ProductRelationsNonSync(
//            [new CompositionNonSync(1, $material1, 30), new CompositionNonSync(2, $material2, 70)],
//            new Properties(1, 'test_name', 'test_value')
//        );
//
//        $this->em()->persist($material1);
//        $this->em()->persist($material2);
//        $this->em()->persist($product);
//        $this->em()->flush();
//
//        $this->assertNotNull($product->compositions[0]);
//        $this->assertNotNull($product->compositions[1]);
//        $this->assertNotNull($product->properties);
//
//        $productSr = $this->searchById($product->id, $product::class);
//        $this->assertNotNull($productSr);
//        $this->assertCount(2, $productSr->compositions);
//        $this->assertNotNull($productSr->compositions[0]);
//        $this->assertNotNull($productSr->compositions[1]);
//        $this->assertNotNull($productSr->properties);
//
//
//        $this->assertEquals($product->id, $productSr->id);
//        $this->assertEquals($product->compositions[0]->id, $productSr->compositions[0]->id);
//        $this->assertEquals($product->compositions[0]->value, $productSr->compositions[0]->value);
//        $this->assertEquals($product->compositions[0]->material->name, $productSr->compositions[0]->material->name);
//
//        $this->assertEquals($product->compositions[1]->id, $productSr->compositions[1]->id);
//        $this->assertEquals($product->compositions[1]->value, $productSr->compositions[1]->value);
//        $this->assertEquals($product->compositions[1]->material->name, $productSr->compositions[1]->material->name);
//
//        $this->assertEquals($product->properties->name, $productSr->properties->name);
//        $this->assertEquals($product->properties->value, $productSr->properties->value);
//
//
//        $material   = $product->compositions[1]->material;
//        $this->assertEquals('cotton', $material->name);
//
//        $material->name = 'silk';
//        $this->em()->persist($material);
//        $this->em()->flush();
//
//        $product    = $this->find($product->id, ProductRelationsNonSync::class);
//        $this->assertNotNull($product);
//        $this->assertNotNull($product->compositions[1]);
//
//        $this->assertEquals('silk', $product->compositions[1]->material->name);
//
//        $productSr = $this->searchById($product->id, $product::class);
//        $this->assertNotNull($productSr);
//        $this->assertNotNull($productSr->compositions[1]);
//        $this->assertEquals('cotton', $productSr->compositions[1]->material->name);
//
//
//        $product->compositions->remove(0);
//
//        $this->em()->flush();
//        $this->em()->clear();
//
//        $product    = $this->find($product->id, ProductRelationsNonSync::class);
//        $this->assertNotNull($product);
//        $this->assertCount(1, $product->compositions);
//
//        $productSr = $this->searchById($product->id, $product::class);
//        $this->assertNotNull($productSr);
//        $this->assertCount(2, $productSr->compositions);
//    }
//
    protected function assertProductsEquals(Product $product, Product $productSr) : void
    {
        $this->assertEquals($product->id, $productSr->id);

        if($product->custom_id !== null) {
            $this->assertNotNull($productSr->custom_id);
            $this->assertTrue($product->custom_id->equals($productSr->custom_id));
        }
        else {
            $this->assertNull($productSr->custom_id);
        }


        $this->assertEquals($product->pattern, $productSr->pattern);

        if($product->price !== null) {
            $this->assertNotNull($productSr->price);
            $this->assertEquals($product->price->price, $productSr->price->price);
            $this->assertEquals($product->price->currency, $productSr->price->currency);
        }
        else {
            $this->assertNull($productSr->price);
        }

        $this->assertCount(count($product->colors), $productSr->colors);
        for($i=0; $i<count($product->colors); $i++){
            $this->assertEquals($product->colors[$i], $productSr->colors[$i]);
        }

        $this->assertCount(count($product->photos), $productSr->photos);
        for($i=0; $i<count($product->photos); $i++){
            $this->assertEquals($product->photos[$i]->size, $productSr->photos[$i]->size);
            $this->assertEquals($product->photos[$i]->url, $productSr->photos[$i]->url);
        }

        $this->assertCount(count($product->compositions), $productSr->compositions);
        for($i=0; $i<count($product->compositions); $i++){
            $this->assertNotNull($productSr->compositions[$i]);
            $this->assertNotNull($product->compositions[$i]);

            $this->assertEquals($product->compositions[$i]->id, $productSr->compositions[$i]->id);
            $this->assertEquals($product->compositions[$i]->material->id, $productSr->compositions[$i]->material->id);
            $this->assertEquals($product->compositions[$i]->material->name, $productSr->compositions[$i]->material->name);
        }

        if($product->properties !== null) {
            $this->assertNotNull($productSr->properties);
            $this->assertEquals($product->properties->id, $productSr->properties->id);
            $this->assertEquals($product->properties->name, $productSr->properties->name);
            $this->assertEquals($product->properties->value, $productSr->properties->value);
        }
        else {
            $this->assertNull($productSr->properties);
        }
    }


    protected function em() : EntityManagerInterface
    {
        /** @var EntityManagerInterface $em */
        $em         = $this->getContainer()->get(EntityManagerInterface::class);
        $events = ['postPersist', 'postUpdate', 'preRemove', 'postRemove', 'postFlush'];
        foreach ($events as $event) {
            $searched   = array_find($em->getEventManager()->getListeners($event), fn($ls) => $ls instanceof EventListener);
            if($searched !== null) {
                $events = ['postPersist', 'postUpdate', 'preRemove', 'postRemove', 'postFlush'];
                $em->getEventManager()->removeEventListener($events, $searched);
            }
        }


//        $searched   = array_find($em->getEventManager()->getListeners('postFlush'), fn($ls) => $ls instanceof EventListener);
//        if($searched !== null) {
//            $events = ['postPersist', 'postUpdate', 'preRemove', 'postRemove', 'postFlush'];
//            $em->getEventManager()->removeEventListener($events, $searched);
//        }

        return $em;
    }


    protected function checkIndex() : void
    {
        /** @var CollectionManager $manager */
        $manager    = $this->getContainer()->get(CollectionManager::class);

        $classes    = [
            Product::class,
            ProductNonSync::class,
            ProductRelationsNonSync::class,
            ProductRelationsNonSync::class,
        ];

        foreach ($classes as $class) {
            if($manager->exists($class)) {
                $manager->delete($class);
            }

            $manager->create($class);
        }
    }


    protected function getTestProduct(bool $index = true) : Product
    {
        $em         = $this->em();
        $material1  = new Material( 'denim');
        $material2  = new Material('cotton');

        $product    = new Product(
            new CustomId('01944071-3781-70e3-89aa-f00b80fd401d'),
            [Color::White, Color::Red],
            [new Photo(100, 'test_url')],
            Pattern::Animal,
            new Price(30, 'eur'),
            [new Composition( $material1, 30), new Composition( $material2, 70)],
            new Properties('test_name', 'test_value')
        );


        $em->persist($product);
        $em->flush();
        $em->clear();

        if($index) {
            $this->indexer()->persist($product);
            $this->indexer()->flush();
        }

        return $product;
    }

    /**
     * @template T of object
     * @param ?int $id
     * @param class-string<T> $finder
     * @return T|null
     * @throws \Http\Client\Exception
     * @throws \Typesense\Exceptions\TypesenseClientError
     */
    protected function searchById(?int $id, string $finder) : object|null
    {
        $result = $this->finder($finder)->query()->filterBy('id :=' . $id)->getResult();

        if(empty($result->hits)) {
            return null;
        }

        return $result->hits[0]->document;
    }

    /**
     * @template T of object
     * @param int|null $id
     * @param class-string<T> $class
     * @return T|null
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function find(?int $id, string $class) : object|null
    {
        return $this->em()->find($class, (int)$id);
    }

    /**
     * @template T of object
     * @param class-string<T> $finder
     * @return Finder<T>
     */
    protected function finder(string $finder) : Finder
    {
        /** @var FinderFactory $factory */
        $factory    = $this->getContainer()->get(FinderFactory::class);

        return $factory->create($finder);
    }

    protected function indexer() : Indexer
    {
        /** @var Indexer $indexer */
        $indexer   = $this->getContainer()->get(Indexer::class);

        return $indexer;
    }

    protected function listener() : EventListener
    {
        /** @var EventListener $indexer */
        $indexer   = $this->getContainer()->get(EventListener::class);

        return $indexer;
    }

    protected function manager() : CollectionManager
    {
        /** @var CollectionManager $manager */
        $manager   = $this->getContainer()->get(CollectionManager::class);

        return $manager;
    }

    protected function transformer() : Transformer
    {
        /** @var Transformer $transformer */
        $transformer = $this->getContainer()->get(Transformer::class);

        return $transformer;
    }
}